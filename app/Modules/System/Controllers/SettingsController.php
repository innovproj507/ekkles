<?php
/**
 * SettingsController.php — Configuración de la Iglesia
 * @package App\Modules\System\Controllers
 */
namespace App\Modules\System\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Modules\System\Models\AuditLog;
use App\Modules\System\Models\Settings;

class SettingsController extends Controller
{
    private Settings $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new Settings();
    }

    public function index(Request $request, Response $response): void
    {
        // Obtener los datos de la iglesia actual
        $church = $this->settingsModel->getChurchData($this->churchId());
        
        $this->view('System::settings/index', [
            'title'  => 'Configuración de la Iglesia',
            'church' => $church
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();

        $validator = Validator::make($data, [
            'name'     => 'required|min:2|max:150',
            'email'    => 'email',
        ]);

        if ($validator->fails()) {
            Session::flash('error', implode(' ', $validator->allErrors()));
            $response->redirect('/configuracion');
            return;
        }

        $churchId = $this->churchId();
        $church   = $this->settingsModel->getChurchData($churchId);

        $updateData = [
            'name'     => trim($data['name']),
            'slogan'   => trim($data['slogan'] ?? ''),
            'address'  => trim($data['address'] ?? ''),
            'phone'    => trim($data['phone'] ?? ''),
            'email'    => trim($data['email'] ?? ''),
            'website'  => trim($data['website'] ?? ''),
        ];

        // Manejo del logo
        if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['logo']['name']);
            $ext      = strtolower($fileInfo['extension']);
            
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                // Borrar logo anterior si existe
                if (!empty($church['logo']) && file_exists(dirname(__DIR__, 4) . '/public/uploads/' . $church['logo'])) {
                    @unlink(dirname(__DIR__, 4) . '/public/uploads/' . $church['logo']);
                }

                $newFileName = 'logo_church_' . $churchId . '_' . time() . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                    $updateData['logo'] = 'logos/' . $newFileName;
                }
            } else {
                Session::flash('error', 'El formato del logo debe ser JPG, PNG o WEBP.');
                $response->redirect('/configuracion');
                return;
            }
        }

        $this->settingsModel->updateChurchData($churchId, $updateData);
        
        // Actualizar el nombre en la sesión de la iglesia actual si está en uso
        if (Session::get('church_id') == $churchId) {
            // Podríamos actualizar algo extra si la UI usa el nombre de la sesión
        }

        AuditLog::record('update', 'system', null, 'churches', $churchId);
        $this->redirectWithSuccess('/configuracion', 'Configuración de la iglesia actualizada correctamente.');
    }
}
