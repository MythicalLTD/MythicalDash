<?php
namespace MythicalDash;

class AddonsManager
{
    private $addonsPath;
    private $loadedAddons = [];

    public function __construct($addonsPath = '../addons') {
        $this->addonsPath = __DIR__ . "/$addonsPath";
    }

    public function loadAddons() {
        $addonFolders = glob($this->addonsPath . '/*', GLOB_ONLYDIR);

        foreach ($addonFolders as $addonFolder) {
            $addonDetailsFile = $addonFolder . '/init.php';

            if (file_exists($addonDetailsFile)) {
                $addonDetails = include $addonDetailsFile;

                if (is_array($addonDetails) && !empty($addonDetails)) {
                    $this->loadedAddons[] = [
                        'details' => $addonDetails,
                        'folder' => $addonFolder,
                    ];
                }
            }
        }

        return $this->loadedAddons;
    }

    public function processAddonRoutes($router) {
        foreach ($this->loadedAddons as $addon) {
            $routesFile = $addon['folder'] . '/routes.php';

            if (file_exists($routesFile)) {
                $addonRoutes = include $routesFile;

                foreach ($addonRoutes as $route) {
                    $router->add($route['path'], function () use ($route, $addon) {
                        $viewFile = $addon['folder'] . '/view/' . $route['view'];

                        if (file_exists($viewFile)) {
                            require $viewFile;
                        } else {
                            header("location: /404");
                        }
                    });
                }
            }
        }
    }
}

?>