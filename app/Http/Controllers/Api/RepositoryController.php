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
class RepositoryController extends BaseController {
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Satis\ConfigManager $configManager
     * @param string|null $repositoryId
     *
     * @throws \HttpInvalidParamException
     */
    protected function addOrUpdateRepository(Request $request, ConfigManager $configManager, $repositoryId = null) {
        $input = new Collection($request->all());

        $validator = Validator::make(
            $request->all(),
            ['url' => 'required|url'],
            [
                'url.required' => 'Repository url must be provided.',
                'url.url' => 'Repository url must be a valid URL address.'
            ]
        );

        if($validator->fails()) {
            Response::json($validator->errors()->first('url'))
                ->setStatusCode(400)
                ->send();
        } else {
            try {
                $output = $configManager->addOrUpdateRepository($repositoryId, $input);

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
     * @param string|null $repositoryId
     */
    public function get(ConfigManager $configManager, $repositoryId = null) {
        try {
            $repositories = $configManager->getRepositories($repositoryId);

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
        $this->addOrUpdateRepository($request, $configManager, null);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Satis\ConfigManager $configManager
     * @param string $repositoryId
     *
     * @throws \HttpInvalidParamException
     */
    public function update(Request $request, ConfigManager $configManager, $repositoryId) {
        $this->addOrUpdateRepository($request, $configManager, $repositoryId);
    }

    /**
     * @param \App\Satis\ConfigManager $configManager
     * @param string $repositoryId
     */
    public function delete(ConfigManager $configManager, $repositoryId) {
        try {
            $configManager->deleteRepository($repositoryId);

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
