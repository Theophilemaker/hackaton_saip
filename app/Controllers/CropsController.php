<?php
namespace App\Controllers;

require_once __DIR__ . '/CropController.php';

/**
 * Alias controller — makes /crops/* routes work
 * by delegating everything to CropController.
 * URL /crops        → CropController::index()
 * URL /crops/plant  → CropController::plant()
 * etc.
 */
class CropsController extends CropController {
    // Inherits ALL methods — nothing else needed
}