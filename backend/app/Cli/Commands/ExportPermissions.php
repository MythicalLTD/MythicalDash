<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Cli\CommandBuilder;

class ExportPermissions extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $app = App::getInstance();
        $app->send('&a&l✓ Exporting permissions...');

        /**
         * Permission Nodes Exporter
         * Reads permission_nodes.txt and exports to PHP, TypeScript files, and documentation.
         */

        // Read the permission nodes file
        $dir = getcwd();
        $permissionNodesFile = $dir . '/permission_nodes.txt';
        $permissionNodes = [];
        $permissionMetadata = [];

        if (!file_exists($permissionNodesFile)) {
            $app->send('&c&l✗ Permission nodes file not found!');

            return;
        }

        $lines = file($permissionNodesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue; // Skip empty lines and comments
            }

            if (strpos($line, '=') !== false) {
                // Parse the new format: KEY=value | category | description
                $parts = explode('=', $line, 2);
                $key = trim($parts[0]);
                $value = trim($parts[1]);

                // Parse metadata if available
                $metadata = [];
                if (strpos($value, '|') !== false) {
                    $metadataParts = explode('|', $value);
                    $value = trim($metadataParts[0]);
                    $metadata['category'] = isset($metadataParts[1]) ? trim($metadataParts[1]) : 'General';
                    $metadata['description'] = isset($metadataParts[2]) ? trim($metadataParts[2]) : '';
                } else {
                    $metadata['category'] = 'General';
                    $metadata['description'] = '';
                }

                $permissionNodes[$key] = $value;
                $permissionMetadata[$key] = $metadata;
            }
        }

        if (empty($permissionNodes)) {
            $app->send('&c&l✗ No permission nodes found in ' . $permissionNodesFile . '!');

            return;
        }

        // Generate PHP class file
        $phpContent = self::generatePHPFile($permissionNodes, $permissionMetadata);
        $phpFile = $dir . '/backend/app/Permissions.php';
        if (file_put_contents($phpFile, $phpContent)) {
            $app->send('&a&l✓ Generated PHP file: ' . $phpFile);
        } else {
            $app->send('&c&l✗ Failed to write PHP file: ' . $phpFile);
        }

        // Generate TypeScript file
        $tsContent = self::generateTypeScriptFile($permissionNodes, $permissionMetadata);
        $tsFile = $dir . '/frontend/src/mythicaldash/Permissions.ts';
        if (file_put_contents($tsFile, $tsContent)) {
            $app->send('&a&l✓ Generated TypeScript file: ' . $tsFile);
        } else {
            $app->send('&c&l✗ Failed to write TypeScript file: ' . $tsFile);
        }

        // Generate README documentation
        $readmeContent = self::generateReadmeDocumentation($permissionNodes, $permissionMetadata);
        $readmeFile = $dir . '/docs/PERMISSIONS.md';

        // Ensure docs directory exists
        if (!is_dir(dirname($readmeFile))) {
            mkdir(dirname($readmeFile), 0755, true);
        }

        if (file_put_contents($readmeFile, $readmeContent)) {
            $app->send('&a&l✓ Generated README documentation: ' . $readmeFile);
        } else {
            $app->send('&c&l✗ Failed to write README file: ' . $readmeFile);
        }

        $app->send('');
        $app->send('');
        $app->send('&a&l✓ Export completed successfully!');
        $app->send('&7Generated files:');
        $app->send('&8├─ &aPHP: &f' . $phpFile);
        $app->send('&8├─ &bTypeScript: &f' . $tsFile);
        $app->send('&8└─ &eREADME: &f' . $readmeFile);
        $app->send('');
        $app->send('&7Statistics:');
        $app->send('&8├─ &ePermission nodes: &f' . count($permissionNodes));
        $app->send('&8├─ &eCategories: &f' . count(array_unique(array_column($permissionMetadata, 'category'))));
        $app->send('&8└─ &eWith descriptions: &f' . count(array_filter($permissionMetadata, fn ($meta) => !empty($meta['description']))));
        $app->send('');
        $app->send('&6⚠️  Frontend requires to be rebuilt!');
    }

    public static function getDescription(): string
    {
        return 'Export permission nodes to PHP, TypeScript files, and documentation';
    }

    public static function getSubCommands(): array
    {
        return [];
    }

    private static function generatePHPFile(array $permissionNodes, array $permissionMetadata): string
    {
        $phpContent = "<?php\n\n";
        $phpContent .= "/*\n";
        $phpContent .= " * This file is part of MythicalDash.\n";
        $phpContent .= " * Please view the LICENSE file that was distributed with this source code.\n";
        $phpContent .= " *\n";
        $phpContent .= " * # MythicalSystems License v2.0\n";
        $phpContent .= " *\n";
        $phpContent .= " * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman\n";
        $phpContent .= " *\n";
        $phpContent .= " * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.\n";
        $phpContent .= " */\n\n";

        $phpContent .= "/**\n";
        $phpContent .= " * Permission Nodes Constants\n";
        $phpContent .= " * Auto-generated from permission_nodes.txt\n";
        $phpContent .= " */\n\n";

        $phpContent .= "/**\n";
        $phpContent .= " * ⚠️  WARNING: Do not modify this file manually!\n";
        $phpContent .= " * This file is auto-generated from permission_nodes.txt\n";
        $phpContent .= " * Use 'php mythicaldash permissionExport' to regenerate this file\n";
        $phpContent .= " * Manual modifications will be overwritten on next generation.\n";
        $phpContent .= " */\n\n";

        $phpContent .= "namespace MythicalDash;\n\n";

        $phpContent .= "class Permissions\n";
        $phpContent .= "{\n";

        // Group permissions by category
        $categories = [];
        foreach ($permissionNodes as $key => $value) {
            $category = $permissionMetadata[$key]['category'] ?? 'General';
            $description = $permissionMetadata[$key]['description'] ?? '';
            $categories[$category][] = [
                'key' => $key,
                'value' => $value,
                'description' => $description,
            ];
        }

        // Generate constants grouped by category
        foreach ($categories as $category => $permissions) {
            $phpContent .= '    // ' . $category . " Permissions\n";
            foreach ($permissions as $permission) {
                if (!empty($permission['description'])) {
                    $phpContent .= '    /** ' . $permission['description'] . " */\n";
                }
                $phpContent .= '    public const ' . $permission['key'] . " = '" . $permission['value'] . "';\n";
            }
            $phpContent .= "\n";
        }

        // Add getAll() static method
        $phpContent .= "    /**\n";
        $phpContent .= "     * Returns all permission nodes with metadata.\n";
        $phpContent .= "     * @return array\n";
        $phpContent .= "     */\n";
        $phpContent .= "    public static function getAll(): array\n";
        $phpContent .= "    {\n";
        $phpContent .= "        return [\n";
        foreach ($permissionNodes as $key => $value) {
            $category = addslashes($permissionMetadata[$key]['category'] ?? 'General');
            $description = addslashes($permissionMetadata[$key]['description'] ?? '');
            $phpContent .= "            [\n";
            $phpContent .= "                'constant' => '$key',\n";
            $phpContent .= "                'value' => self::$key,\n";
            $phpContent .= "                'category' => '$category',\n";
            $phpContent .= "                'description' => '$description'\n";
            $phpContent .= "            ],\n";
        }
        $phpContent .= "        ];\n";
        $phpContent .= "    }\n";

        $phpContent .= "}\n";

        return $phpContent;
    }

    private static function generateTypeScriptFile(array $permissionNodes, array $permissionMetadata): string
    {
        $tsContent = "/**\n";
        $tsContent .= " * Permission Nodes Constants\n";
        $tsContent .= " * Auto-generated from permission_nodes.txt\n";
        $tsContent .= " */\n\n";

        $tsContent .= "/**\n";
        $tsContent .= " * ⚠️  WARNING: Do not modify this file manually!\n";
        $tsContent .= " * This file is auto-generated from permission_nodes.txt\n";
        $tsContent .= " * Use 'php mythicaldash permissionExport' to regenerate this file\n";
        $tsContent .= " * Manual modifications will be overwritten on next generation.\n";
        $tsContent .= " */\n\n";

        $tsContent .= "class Permissions {\n";

        // Group permissions by category
        $categories = [];
        foreach ($permissionNodes as $key => $value) {
            $category = $permissionMetadata[$key]['category'] ?? 'General';
            $description = $permissionMetadata[$key]['description'] ?? '';
            $categories[$category][] = [
                'key' => $key,
                'value' => $value,
                'description' => $description,
            ];
        }

        foreach ($categories as $category => $permissions) {
            $tsContent .= '    // ' . $category . " Permissions\n";
            foreach ($permissions as $permission) {
                if (!empty($permission['description'])) {
                    $tsContent .= '    /** ' . $permission['description'] . " */\n";
                }
                $tsContent .= '    public static ' . $permission['key'] . " = '" . $permission['value'] . "';\n";
            }
            $tsContent .= "\n";
        }

        // Add getAll() static method
        $tsContent .= "    /**\n";
        $tsContent .= "     * Returns all permission nodes with metadata.\n";
        $tsContent .= "     */\n";
        $tsContent .= "    public static getAll(): Array<{ constant: string; value: string; category: string; description: string }> {\n";
        $tsContent .= "        return [\n";
        foreach ($permissionNodes as $key => $value) {
            $category = addslashes($permissionMetadata[$key]['category'] ?? 'General');
            $description = addslashes($permissionMetadata[$key]['description'] ?? '');
            $tsContent .= "            { constant: '$key', value: Permissions.$key, category: '$category', description: '$description' },\n";
        }
        $tsContent .= "        ];\n";
        $tsContent .= "    }\n";

        $tsContent .= "}\n\n";
        $tsContent .= "export default Permissions;\n";

        return $tsContent;
    }

    private static function generateReadmeDocumentation(array $permissionNodes, array $permissionMetadata): string
    {
        $readme = "# MythicalDash Permission Nodes\n\n";
        $readme .= "This document provides a comprehensive overview of all permission nodes used in MythicalDash.\n\n";
        $readme .= "## Overview\n\n";
        $readme .= '- **Total Permissions:** ' . count($permissionNodes) . "\n";
        $readme .= '- **Categories:** ' . count(array_unique(array_column($permissionMetadata, 'category'))) . "\n";
        $readme .= '- **With Descriptions:** ' . count(array_filter($permissionMetadata, fn ($meta) => !empty($meta['description']))) . "\n\n";

        $readme .= "## Format\n\n";
        $readme .= "Each permission node follows this format:\n";
        $readme .= "```\n";
        $readme .= "CONSTANT_NAME=permission.node.value | Category | Description\n";
        $readme .= "```\n\n";

        $readme .= "## Usage\n\n";
        $readme .= "### PHP\n";
        $readme .= "```php\n";
        $readme .= "use MythicalDash\\Permissions;\n\n";
        $readme .= "// Check if user has permission\n";
        $readme .= "if (auth()->user()->hasPermission(Permissions::ADMIN_DASHBOARD_VIEW)) {\n";
        $readme .= "    // User can view dashboard\n";
        $readme .= "}\n";
        $readme .= "```\n\n";

        $readme .= "### TypeScript/JavaScript\n";
        $readme .= "```typescript\n";
        $readme .= "import Permissions from '@/mythicaldash/Permissions';\n\n";
        $readme .= "// Check if user has permission\n";
        $readme .= "if (auth.user.hasPermission(Permissions.ADMIN_DASHBOARD_VIEW)) {\n";
        $readme .= "    // User can view dashboard\n";
        $readme .= "}\n";
        $readme .= "```\n\n";

        // Group permissions by category
        $categories = [];
        foreach ($permissionNodes as $key => $value) {
            $category = $permissionMetadata[$key]['category'] ?? 'General';
            $description = $permissionMetadata[$key]['description'] ?? '';
            $categories[$category][] = [
                'key' => $key,
                'value' => $value,
                'description' => $description,
            ];
        }

        // Generate category sections
        foreach ($categories as $category => $permissions) {
            $readme .= '## ' . $category . "\n\n";

            if ($category === 'Dashboard Components') {
                $readme .= "These permissions control access to various dashboard components and widgets.\n\n";
            } elseif ($category === 'Dashboard') {
                $readme .= "These permissions control access to the main dashboard functionality.\n\n";
            }

            $readme .= "| Permission | Node | Description |\n";
            $readme .= "|------------|------|-------------|\n";

            foreach ($permissions as $permission) {
                $description = !empty($permission['description']) ? $permission['description'] : '-';
                $readme .= '| `' . $permission['key'] . '` | `' . $permission['value'] . '` | ' . $description . " |\n";
            }

            $readme .= "\n";
        }

        $readme .= "## Adding New Permissions\n\n";
        $readme .= "To add a new permission node:\n\n";
        $readme .= "1. Edit `permission_nodes.txt` in the root directory\n";
        $readme .= "2. Add your permission in the format: `CONSTANT_NAME=permission.node.value | Category | Description`\n";
        $readme .= "3. Run `php mythicaldash permissionExport` to regenerate all files\n";
        $readme .= "4. Rebuild the frontend if necessary\n\n";

        $readme .= "## File Locations\n\n";
        $readme .= "- **Source:** `permission_nodes.txt` (root directory)\n";
        $readme .= "- **PHP:** `backend/app/Permissions.php`\n";
        $readme .= "- **TypeScript:** `frontend/src/mythicaldash/Permissions.ts`\n";
        $readme .= "- **Documentation:** `docs/PERMISSIONS.md` (this file)\n\n";

        $readme .= "## Auto-Generation\n\n";
        $readme .= '⚠️ **Important:** All generated files are automatically created from `permission_nodes.txt`. ';
        $readme .= "Manual modifications to the generated files will be overwritten when the export command is run.\n\n";

        $readme .= "---\n\n";
        $readme .= '*This documentation was auto-generated on ' . date('Y-m-d H:i:s') . "*\n";

        return $readme;
    }
}
