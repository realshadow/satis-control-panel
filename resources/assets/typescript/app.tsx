/// <reference path="../../../typings/tsd.d.ts"/>

import ReactDom = require('react-dom');
import React = require('react');
import ReactMixin = require('react-mixin');
import axios = require('axios');
import Postal = require('postal');
import NotificationSystem = require('react-notification-system');

import {InfoPanel} from './flux/components/InfoPanel';
import {Repositories} from './flux/components/Repositories';
import {Packages} from './flux/components/Packages';
import {ActionPanel} from './flux/components/ActionPanel';
import {RepositoryModal, PackageModal} from './flux/components/Modal';
import {Overlay} from './flux/components/Overlay';
import {App} from './helpers';
import EventHandler = require('./flux/mixins/EventHandler');

interface AppState extends SatisConfigInterface {
    locked?: boolean;
}

let Application = React.createClass({
	notificationSystem: null,
	lockMessage: null,	
	
	getInitialState(): AppState {
		if(Satis.config && Satis.config.require) {
			Satis.config.require = App.makePackages(Satis.config.require);
		}

	    let state: AppState = Satis.config || {
            name: null,
            homepage: null,
            repositories: [],
		    require: {}
        };

        state.locked = Satis.locked;

		return state;
	},
	
	toggleOverlay(show: boolean): void {
		let state = this.refs['overlay'].state;
		
		state.show = show;
		
		this.refs['overlay'].setState(state);		
	},
	
	notify(notification: NotificationSystemMessage): NotificationSystemInterface<NotificationSystemProps> {
		if(typeof notification.autoDismiss === 'undefined') {
			notification.autoDismiss = 3;
		}

		if(typeof notification.dismissible === 'undefined') {
			notification.dismissible = true;
		}

		return this.notificationSystem.addNotification({
			message: notification.message,
			position: notification.position || 'tc',
			dismissible: notification.dismissible,
			autoDismiss: notification.autoDismiss,
			level: notification.level || 'success'
		});
	},
	
	onBuild(buildContext: BuildContextInterface): void {
		let itemId: string = buildContext.item ? '?what=' + buildContext.item : '';
		let options: Object = {
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		};
		
		this.toggleOverlay(true);
		
		this.onLock();
		
		axios.post('/control-panel/build-' + buildContext.type + itemId, {}, options)
			.then((response: axios.Response) => {
				this.toggleOverlay(false);

				this.onUnlock();
			})
			.catch((response: axios.Response) => {
				this.toggleOverlay(false);

				this.onUnlock();
			});
	},
	
	onLock(): void {
		if(!this.lockMessage) {
			this.lockMessage = this.notify({
				message: 'Satis is currently building your repositories and thus all actions are blocked until said build finishes.',
				level: 'info',
				dismissible: false,
				autoDismiss: 0
			});
		}
	},
	
	onUnlock(): void {
		if(this.lockMessage) {
			this.notificationSystem.removeNotification(this.lockMessage);
			
			this.lockMessage = null;
		}
	},
	
	connectToNodeServer(): void {
		try {
			var socket = io.connect(Satis.node_server.host + ':' + Satis.node_server.port);

			socket.on('notification', (data) => {
				if(typeof data.locked === 'undefined') return;

				let state = this.state;

				state.show = data.locked;

				if(state.show === true) {
					this.onLock();
				} else if(state.show === false) {
					this.onUnlock();
				}

				this.setState(state);
			});
		} catch(e) {
			this.notify({
				message: 'Could not connect to NodeJS server, functionality might be limited.',
				level: 'warning'
			});			
		}		
	},

	filterRepositories(event): void {
		let value: string = event.target.value.trim();

		this.publish(App.REPOSITORY_FILTER, value);
	},

	filterPackages(event): void {
		let value: string = event.target.value.trim();

		this.publish(App.PACKAGE_FILTER, value);
	},

	componentDidMount(): void {
		this.notificationSystem = this.refs.notificationSystem;

		this.subscribe(App.NOTIFICATION, this.notify);
		this.subscribe(App.TRIGGER_BUILD, this.onBuild);

		if(Satis.loaded === false) {
			this.notify({
				message: Satis.message,
				level: 'error'
			});
		} else if(Satis.locked) {
			this.onLock();
		}
		
		this.connectToNodeServer();
	},
    
    render(): JSX.Element {
        return (
			<div className="panel panel-base with-nav-tabs panel-default">
				<InfoPanel name={this.state.name} homepage={this.state.homepage} />
				
				<div className="panel-body">
					<div className="tab-content">
						<div className="tab-pane active" id="repositories">
							<ActionPanel
									button="Add repository"
									placeholder="Filter repositories by URL"
									modalId={App.REPOSITORY_MODAL_ID}
									onFilter={this.filterRepositories}
									activateForm={App.ACTIVATE_REPOSITORY_FORM}
									buildContext="private"
									enableBuild={true}
							/>

							<Repositories repositories={this.state.repositories} />
						</div>
						<div className="tab-pane" id="packages">
							<ActionPanel
									button="Add package"
									placeholder="Filter packages by name"
									modalId={App.PACKAGE_MODAL_ID}
									onFilter={this.filterPackages}
									activateForm={App.ACTIVATE_PACKAGE_FORM}
									buildContext="public"
									enableBuild={true}
							/>

							<Packages packages={this.state.require} />
						</div>
					</div>
				</div>
				
				<NotificationSystem ref="notificationSystem" />
				<RepositoryModal />
				<PackageModal />
				<Overlay
				    ref="overlay"
				    show={this.state.locked}
				    onLock={this.onLock}
				    onUnlock={this.onUnlock}
				    notify={this.notify}
                />
			</div>
		);
	}	
});

// since we use createClass function we can not use decorators
ReactMixin(Application.prototype, EventHandler.Mixin);

ReactDom.render(<Application />, document.querySelector('.container'));