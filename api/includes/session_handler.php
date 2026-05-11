<?php
/**
 * FASEEH DATABASE SESSION HANDLER
 * Ensures sessions work perfectly on Vercel's serverless environment.
 */

class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row['data'] : '';
    }

    public function write($id, $data): bool {
        $access = time();
        $stmt = $this->pdo->prepare("INSERT INTO sessions (id, data, last_access) VALUES (?, ?, ?) 
                                     ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, last_access = EXCLUDED.last_access");
        
        // Handle MySQL vs Postgres syntax
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') {
            $stmt = $this->pdo->prepare("INSERT INTO sessions (id, data, last_access) VALUES (?, ?, ?) 
                                         ON DUPLICATE KEY UPDATE data = VALUES(data), last_access = VALUES(last_access)");
        }
        
        return $stmt->execute([$id, $data, $access]);
    }

    public function destroy($id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($maxlifetime): int|false {
        $old = time() - $maxlifetime;
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_access < ?");
        $stmt->execute([$old]);
        return true; // Return true for compatibility with PHP 8.1+
    }
}
