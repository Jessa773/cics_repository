<?php

class GitConfig {
    public $repoPath;
    public $git;

    public function __construct() {
        // Set the repository path
        $this->repoPath = dirname(dirname(__DIR__)) . '/repositories';
        
        // Create repositories directory if it doesn't exist
        if (!file_exists($this->repoPath)) {
            mkdir($this->repoPath, 0777, true);
        }
    }

    public function initRepository($projectName) {
        try {
            $projectPath = $this->repoPath . '/' . $projectName;
            
            if (!file_exists($projectPath)) {
                mkdir($projectPath, 0777, true);
                exec('git init ' . escapeshellarg($projectPath));
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log('Git initialization error: ' . $e->getMessage());
            return false;
        }
    }

    public function addAndCommit($projectName, $filename, $content, $message) {
        try {
            $projectPath = $this->repoPath . '/' . $projectName;
            $filePath = $projectPath . '/' . $filename;

            // Write content to file
            file_put_contents($filePath, $content);

            // Git commands
            $commands = [
                'cd ' . escapeshellarg($projectPath),
                'git add ' . escapeshellarg($filename),
                'git commit -m ' . escapeshellarg($message)
            ];

            // Execute commands
            exec(implode(' && ', $commands), $output, $returnVar);
            
            return $returnVar === 0;
        } catch (Exception $e) {
            error_log('Git commit error: ' . $e->getMessage());
            return false;
        }
    }

    public function getHistory($projectName) {
        try {
            $projectPath = $this->repoPath . '/' . $projectName;
            $command = 'cd ' . escapeshellarg($projectPath) . ' && git log --pretty=format:"%h|%an|%ad|%s"';
            
            exec($command, $output);
            
            $history = [];
            foreach ($output as $line) {
                list($hash, $author, $date, $message) = explode('|', $line);
                $history[] = [
                    'hash' => $hash,
                    'author' => $author,
                    'date' => $date,
                    'message' => $message
                ];
            }
            
            return $history;
        } catch (Exception $e) {
            error_log('Git history error: ' . $e->getMessage());
            return [];
        }
    }
}
