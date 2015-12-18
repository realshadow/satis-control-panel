<?php

namespace App\Http\Controllers\Api;

use App\Satis\Exceptions\PackageBuildFailedException;
use App\Satis\Exceptions\RepositoryNotFoundException;
use Illuminate\Support\Collection;
use Validator;
use Illuminate\Http\Request;
use App\Satis\ConfigManager;
use Response;
use Illuminate\Routing\Controller as BaseController;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class PackageController extends BaseController {
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Satis\ConfigManager $configManager
     * @param string|null $packageId
     *
     * @throws \HttpInvalidParamException
     */
    protected function addOrUpdatePackage(Request $request, ConfigManager $configManager, $packageId = null) {
        $input = new Collection($request->all());

        $validator = Validator::make(
            $request->all(),
            ['name' => 'required|regex:#[A-Za-z0-9][A-Za-z0-9_.-]*/[A-Za-z0-9][A-Za-z0-9_.-]#'],
            [
                'name.required' => 'Package name must be provided.',
                'name.regex' => 'Package name must be in valid format. E.g. organization/package'
            ]
        );

        if($validator->fails()) {
            Response::json($validator->errors()->first('name'))
                ->setStatusCode(400)
                ->send();
        } else {
            try {
                $output = $configManager->addOrUpdatePackage($packageId, $input);

                Response::json(['command_output' => $output])
                    ->send();
            } catch(PackageBuildFailedException $e) {
                Response::json()
                    ->setStatusCode(500)
                    ->send();
            } catch(RepositoryNotFoundException $e) {
                Response::json()
                    ->setStatusCode(404)
                    ->send();
            }
        }
    }

    /**
     * @param \App\Satis\ConfigManager $configManager
     * @param string|null $packageId
     */
    public function get(ConfigManager $configManager, $packageId = null) {
        try {
            $repositories = $configManager->getPackages($packageId);

            Response::make($repositories)
              ->send();
        } catch(RepositoryNotFoundException $e) {
            Response::json()
                ->setStatusCode(404)
                ->send();
        } catch(PackageBuildFailedException $e) {
            Response::json($e->getMessage())
                ->setStatusCode(500)
                ->send();
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Satis\ConfigManager $configManager
     *
     * @throws \HttpInvalidParamException
     */
    public function add(Request $request, ConfigManager $configManager) {
        $this->addOrUpdatePackage($request, $configManager, null);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Satis\ConfigManager $configManager
     * @param string $packageId
     *
     * @throws \HttpInvalidParamException
     */
    public function update(Request $request, ConfigManager $configManager, $packageId) {
        $this->addOrUpdatePackage($request, $configManager, $packageId);
    }

    /**
     * @param \App\Satis\ConfigManager $configManager
     * @param string $packageId
     */
    public function delete(ConfigManager $configManager, $packageId) {
        try {
            $configManager->deletePackage($packageId);

            Response::json()
                ->send();
        } catch(RepositoryNotFoundException $e) {
            Response::json()
                ->setStatusCode(404)
                ->send();
        } catch(PackageBuildFailedException $e) {
            Response::json($e->getMessage())
                ->setStatusCode(500)
                ->send();
        }
	}
}
