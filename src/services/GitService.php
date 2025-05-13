<?php

namespace App\Services;

use GitWrapper\GitWrapper;
use Exception;

class GitService {
    private $wrapper;
    private $repoPath;

    public function __construct($repoPath) {
        $this->wrapper = new GitWrapper();
        $this->repoPath = $repoPath;
    }

    public function clone($url) {
        try {
            $this->wrapper->clone($url, $this->repoPath);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function pull() {
        try {
            $git = $this->wrapper->workingCopy($this->repoPath);
            $git->pull();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function push() {
        try {
            $git = $this->wrapper->workingCopy($this->repoPath);
            $git->add('.');
            $git->commit('Auto commit');
            $git->push();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
