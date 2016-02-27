<?php

namespace App\Http\Controllers;

use App\Satis\BuildContext;
use App\Satis\ConfigManager;
use App\Satis\Context\PrivateRepository;
use App\Satis\Context\PublicRepository;
use App\Satis\Model\ControlPanelConfig;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use JMS\Serializer\Serializer;
use Exception;
use Response;
use Illuminate\Routing\Controller as BaseController;

/**
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class Controller extends BaseController {
	/**
     * @param \App\Satis\ConfigManager $configManager
     * @param \App\Satis\BuildContext $buildContext
     * @param \Illuminate\Http\Request $request
     */
    protected function build(ConfigManager $configManager, BuildContext $buildContext, Request $request) {
        if($request->ajax()) {
            $buildContext->setItemName($request->get('what'));

            $configManager->forceBuild($buildContext);

            Response::json()
                ->send();
        } else {
            Response::json()
                ->setStatusCode(404)
                ->send();
        }
    }

    /**
     * @param \JMS\Serializer\Serializer $serializer
     * @param \App\Satis\Model\ControlPanelConfig $controlPanelConfig
     * @param \App\Satis\ConfigManager $configManager
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Serializer $serializer, ControlPanelConfig $controlPanelConfig,
        ConfigManager $configManager
    ) {

        try {
            $controlPanelConfig
                ->setConfig($configManager->getDefinition())
                ->setRepositoryTypes(config('satis.repository_types'))
                ->isLoaded(true);
        } catch(Exception $e) {
            $message = $e->getMessage();
            if($e instanceof FileNotFoundException) {
                $message = trans('satis.not_found');
            }

            $controlPanelConfig
                ->setMessage($message)
                ->isLoaded(false);
        }

        $controlPanelConfig->setNodeServer(config('satis.node'));
        $controlPanelConfig->isLocked($configManager->isBuilding());

        return view('index', [
            'satis' => $serializer->serialize($controlPanelConfig, 'json'),
            'webpackDevServer' => config('satis.webpack_dev_server'),
            'nodeServer' => config('satis.node.host') . ':' . config('satis.node.port')
        ]);
    }

	/**
     * @param \App\Satis\ConfigManager $configManager
     * @param \Illuminate\Http\Request $request
     */
    public function buildPrivate(ConfigManager $configManager, Request $request) {
        $this->build($configManager, new PrivateRepository(), $request);
    }

	/**
     * @param \App\Satis\ConfigManager $configManager
     * @param \Illuminate\Http\Request $request
     */
    public function buildPublic(ConfigManager $configManager, Request $request) {
        $this->build($configManager, new PublicRepository(), $request);
    }
}
