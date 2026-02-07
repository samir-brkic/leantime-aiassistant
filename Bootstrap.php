<?php

namespace Leantime\Plugins\AIAssistant;

use Leantime\Plugins\AIAssistant\Repositories\Settings as SettingsRepository;
use Leantime\Plugins\AIAssistant\Repositories\Categories as CategoriesRepository;

/**
 * Plugin Bootstrap Class
 * 
 * Handles plugin installation and uninstallation
 */
class Bootstrap
{
    private SettingsRepository $settingsRepo;
    private CategoriesRepository $categoriesRepo;

    public function __construct(SettingsRepository $settingsRepo, CategoriesRepository $categoriesRepo)
    {
        $this->settingsRepo = $settingsRepo;
        $this->categoriesRepo = $categoriesRepo;
    }

    /**
     * Install plugin - create database tables
     */
    public function install(): void
    {
        $this->settingsRepo->installIfNeeded();
        $this->categoriesRepo->installIfNeeded();
    }

    /**
     * Uninstall plugin - clean up (optional)
     */
    public function uninstall(): void
    {
        // Optional: Remove settings and categories tables
        // For now, we keep the data for safety
    }
}

