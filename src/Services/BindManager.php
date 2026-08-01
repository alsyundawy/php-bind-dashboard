<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Config;
use App\Helpers\Database;
use PDO;

/**
 * BIND9 Zone File Manager
 * Generates and manages standard BIND zone files
 */
final class BindManager
{
    private PDO $db;
    private string $zonesDir;
    /** @var array<string, mixed> */
    private array $defaultSoa;
    /** @var list<string> */
    private array $defaultNs;

    public function __construct()
    {
        $this->db = Database::get();
        $this->zonesDir = rtrim((string) Config::get('bind.zones_dir', '/etc/bind/zones'), '/');

        $soa = Config::get('bind.default_soa', []);
        $this->defaultSoa = is_array($soa) ? $soa : [];

        $ns = Config::get('bind.default_ns', []);
        $this->defaultNs = is_array($ns) ? array_values(array_filter($ns, 'is_string')) : [];
    }

    public function listZones(): array
    {
        $stmt = $this->db->query(
            'SELECT z.*, u.username AS created_by_name
             FROM zones z
             LEFT JOIN users u ON z.created_by = u.id
             ORDER BY z.name ASC'
        );
        return $stmt->fetchAll() ?: [];
    }

    public function getZone(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM zones WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getZoneByName(string $name): ?array
    {
        $name = $this->normalizeZoneName($name);
        $stmt = $this->db->prepare('SELECT * FROM zones WHERE name = ? COLLATE NOCASE');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createZone(string $name, string $type = 'master', bool $isReverse = false, ?int $userId = null, string $notes = ''): int
    {
        $name = $this->normalizeZoneName($name);

        $allowedTypes = ['master', 'slave', 'forward'];
        if (!in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException('Invalid zone type. Allowed: master, slave, forward');
        }

        if ($this->getZoneByName($name) !== null) {
            throw new \InvalidArgumentException('Zone already exists: ' . $name);
        }

        if (!Config::get('bind.allow_write', true)) {
            throw new \RuntimeException('Zone writing is disabled in configuration');
        }

        $fileName = $this->zoneFileName($name);
        $filePath = $this->zonesDir . '/' . $fileName;

        if (!is_dir($this->zonesDir)) {
            if (!mkdir($this->zonesDir, 0775, true) && !is_dir($this->zonesDir)) {
                throw new \RuntimeException('Cannot create zones directory: ' . $this->zonesDir);
            }
        }

        $serial = (int) (date('Ymd') . '01');
        $content = $this->generateInitialZoneContent($name, $serial);

        if (file_put_contents($filePath, $content) === false) {
            throw new \RuntimeException('Failed to write zone file: ' . $filePath);
        }

        @chmod($filePath, 0644); // NOSONAR

        $stmt = $this->db->prepare(
            'INSERT INTO zones (name, type, file_path, is_reverse, serial, notes, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            $type,
            $filePath,
            $isReverse ? 1 : 0,
            $serial,
            $notes,
            $userId,
        ]);

        $zoneId = (int) $this->db->lastInsertId();
        $this->updateNamedConfInclude($name, $filePath, $type);

        return $zoneId;
    }

    public function deleteZone(int $id): bool
    {
        $zone = $this->getZone($id);
        if ($zone === null) {
            return false;
        }

        if (!empty($zone['file_path']) && is_file($zone['file_path'])) {
            @unlink($zone['file_path']); // NOSONAR - best-effort delete
        }

        $stmt = $this->db->prepare('DELETE FROM zones WHERE id = ?');
        $stmt->execute([$id]);

        return true;
    }

    public function parseZoneFile(string $filePath): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        $records = [];
        $lines = explode("\n", $content);
        $origin = '';
        $ttl = 3600;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, ';')) {
                continue;
            }

            if (preg_match('/^\$ORIGIN\s+(\S+)/i', $line, $m)) {
                $origin = rtrim($m[1], '.') . '.';
                continue;
            }

            if (preg_match('/^\$TTL\s+(\d+)/i', $line, $m)) {
                $ttl = (int) $m[1];
                continue;
            }

            if (str_starts_with(strtoupper($line), '$GENERATE')) {
                continue;
            }

            $parts = preg_split('/\s+/', $line, 5);
            if ($parts === false || count($parts) < 3) {
                continue;
            }

            $name = $parts[0];
            if ($name === '@') {
                $name = $origin !== '' ? $origin : '@';
            } elseif (!str_ends_with($name, '.')) {
                $name = $name . '.' . ($origin !== '' ? rtrim($origin, '.') : '');
            }

            $idx = 1;
            $recTtl = $ttl;
            $class = 'IN';

            if (isset($parts[$idx]) && ctype_digit($parts[$idx])) {
                $recTtl = (int) $parts[$idx];
                $idx++;
            }

            if (isset($parts[$idx]) && strtoupper($parts[$idx]) === 'IN') {
                $class = 'IN';
                $idx++;
            }

            if (!isset($parts[$idx])) {
                continue;
            }
            $type = strtoupper($parts[$idx]);
            $idx++;

            $rdata = isset($parts[$idx]) ? implode(' ', array_slice($parts, $idx)) : '';

            if (($pos = strpos($rdata, ';')) !== false) {
                $rdata = trim(substr($rdata, 0, $pos));
            }

            $records[] = [
                'name'  => $name,
                'ttl'   => $recTtl,
                'class' => $class,
                'type'  => $type,
                'rdata' => $rdata,
            ];
        }

        return $records;
    }

    public function writeZoneFile(int $zoneId, array $records, int $newSerial): bool
    {
        $zone = $this->getZone($zoneId);
        if ($zone === null || empty($zone['file_path'])) {
            return false;
        }

        $name = $zone['name'];
        $content = $this->buildZoneContent($name, $records, $newSerial);

        if (file_put_contents($zone['file_path'], $content) === false) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE zones SET serial = ?, updated_at = datetime(\'now\') WHERE id = ?'
        );
        $stmt->execute([$newSerial, $zoneId]);

        return true;
    }

    public function reloadZone(int $zoneId): bool
    {
        $zone = $this->getZone($zoneId);
        if ($zone === null) {
            return false;
        }

        $rndc = (string) Config::get('bind.rndc_path', '/usr/sbin/rndc');
        if ($rndc !== '' && is_executable($rndc)) {
            $cmd = escapeshellcmd($rndc) . ' reload ' . escapeshellarg(rtrim((string) $zone['name'], '.'));
            // NOSONAR - intentional controlled shell call with escaped arguments
            exec($cmd . ' 2>&1', $output, $code);
            return $code === 0;
        }

        return true;
    }

    private function normalizeZoneName(string $name): string
    {
        $name = strtolower(trim($name));
        if ($name === '') {
            throw new \InvalidArgumentException('Zone name cannot be empty');
        }
        if (!str_ends_with($name, '.')) {
            $name .= '.';
        }
        if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]*[a-z0-9])?)*\.$/i', $name)
            && !preg_match('/^\d+\.\d+\.\d+\.\d+\.in-addr\.arpa\.$/', $name)
            && !preg_match('/^[0-9a-f:.]+\.ip6\.arpa\.$/i', $name)) {
            if (!str_contains($name, 'in-addr.arpa') && !str_contains($name, 'ip6.arpa')) {
                throw new \InvalidArgumentException('Invalid zone name format');
            }
        }
        return $name;
    }

    private function zoneFileName(string $name): string
    {
        $base = rtrim($name, '.');
        $base = str_replace(['/', '\\'], '_', $base);
        return 'db.' . $base;
    }

    private function generateInitialZoneContent(string $name, int $serial): string
    {
        $soa = $this->defaultSoa;
        $primary = $soa['primary'] ?? 'ns1.example.com.';
        $email = $soa['email'] ?? 'hostmaster.example.com.';
        $ttl = (int) ($soa['ttl'] ?? 3600);

        $lines = [];
        $lines[] = '; Zone file for ' . $name;
        $lines[] = '; Generated by PHP-Bind-Dashboard on ' . date('Y-m-d H:i:s');
        $lines[] = '';
        $lines[] = '$TTL ' . $ttl;
        $lines[] = '@   IN  SOA ' . $primary . ' ' . $email . ' (';
        $lines[] = '            ' . $serial . ' ; Serial';
        $lines[] = '            ' . ($soa['refresh'] ?? 86400) . ' ; Refresh';
        $lines[] = '            ' . ($soa['retry'] ?? 7200) . ' ; Retry';
        $lines[] = '            ' . ($soa['expire'] ?? 3600000) . ' ; Expire';
        $lines[] = '            ' . ($soa['minimum'] ?? 86400) . ' ; Minimum TTL';
        $lines[] = '        )';
        $lines[] = '';

        foreach ($this->defaultNs as $ns) {
            $lines[] = '@   IN  NS  ' . $ns;
        }
        $lines[] = '';

        return implode("\n", $lines) . "\n";
    }

    private function buildZoneContent(string $name, array $records, int $serial): string
    {
        $soa = $this->defaultSoa;
        $primary = $soa['primary'] ?? 'ns1.example.com.';
        $email = $soa['email'] ?? 'hostmaster.example.com.';
        $ttl = (int) ($soa['ttl'] ?? 3600);

        $lines = [];
        $lines[] = '; Zone file for ' . $name;
        $lines[] = '; Generated by PHP-Bind-Dashboard on ' . date('Y-m-d H:i:s');
        $lines[] = '';
        $lines[] = '$ORIGIN ' . $name;
        $lines[] = '$TTL ' . $ttl;
        $lines[] = '';

        $hasSoa = false;
        foreach ($records as $rec) {
            if (strtoupper($rec['type'] ?? '') === 'SOA') {
                $hasSoa = true;
                break;
            }
        }

        if (!$hasSoa) {
            $lines[] = '@   IN  SOA ' . $primary . ' ' . $email . ' (';
            $lines[] = '            ' . $serial . ' ; Serial';
            $lines[] = '            ' . ($soa['refresh'] ?? 86400) . ' ; Refresh';
            $lines[] = '            ' . ($soa['retry'] ?? 7200) . ' ; Retry';
            $lines[] = '            ' . ($soa['expire'] ?? 3600000) . ' ; Expire';
            $lines[] = '            ' . ($soa['minimum'] ?? 86400) . ' ; Minimum TTL';
            $lines[] = '        )';
            $lines[] = '';
        }

        foreach ($records as $rec) {
            $rname = $rec['name'] ?? '@';
            if (str_ends_with($rname, $name)) {
                $rname = substr($rname, 0, -strlen($name));
                if ($rname === '') {
                    $rname = '@';
                } else {
                    $rname = rtrim($rname, '.');
                }
            }

            $line = sprintf(
                "%-30s %6d  IN  %-6s %s",
                $rname,
                (int) ($rec['ttl'] ?? $ttl),
                strtoupper($rec['type'] ?? 'A'),
                $rec['rdata'] ?? ''
            );
            $lines[] = $line;
        }

        $lines[] = '';
        return implode("\n", $lines) . "\n";
    }

    private function updateNamedConfInclude(string $zoneName, string $filePath, string $type): void
    {
        $namedConf = Config::get('bind.named_conf');
        if (empty($namedConf) || !is_writable(dirname((string) $namedConf))) {
            return;
        }

        $zoneShort = rtrim($zoneName, '.');
        $entry = "\nzone \"" . $zoneShort . "\" {\n    type " . $type . ";\n    file \"" . $filePath . "\";\n};\n";

        $current = is_file((string) $namedConf) ? (string) file_get_contents((string) $namedConf) : '';
        if (str_contains($current, 'zone "' . $zoneShort . '"')) {
            return;
        }

        file_put_contents((string) $namedConf, $current . $entry, FILE_APPEND | LOCK_EX);
    }
}
