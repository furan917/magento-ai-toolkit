<?php
/**
 * Magento 2 — registration.php Snippet Reference
 * File: app/code/Vendor/Module/registration.php  ← must be in the module ROOT, not in etc/
 *
 * Registers this directory as a Magento module with the component system.
 *
 * The module name passed to register() MUST exactly match:
 *   - The <module name="..."> value in etc/module.xml
 *   - The directory structure: Vendor_Module → app/code/Vendor/Module/
 *
 * This file is autoloaded by Composer via the "files" entry in composer.json.
 * Do not add any logic here — registration only.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Vendor_Module',
    __DIR__
);
