/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import ReactMixin = require('react-mixin');

import {App} from '../../helpers';
import EventHandler = require('../mixins/EventHandler');
import {RepositoryForm, PackageForm} from './Forms';

interface ModalTriggerProps extends React.Props<any> {
	children?: any;
	repositoryUrl?: string;
	repositoryType?: string;
	packageName?: string;
	packageVersion?: string;
	activeForm: string;
}

@ReactMixin.decorate(EventHandler.Mixin)
export class ModalTrigger extends React.Component<ModalTriggerProps, {}> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void;
    publish: (topic: string, data: any) => void;

	constructor(props: ModalTriggerProps) {
		super(props);

		this.onClick = this.onClick.bind(this);
	}
	
	onClick(): void {
		let data = {};
		switch(this.props.activeForm) {
			case App.ACTIVATE_REPOSITORY_FORM:
				data = { url: this.props.repositoryUrl, type: this.props.repositoryType };
				break;
			case App.ACTIVATE_PACKAGE_FORM:
				data = { name: this.props.packageName, version: this.props.packageVersion };
				break;
		}

		this.publish(this.props.activeForm, data);
	}

	render(): JSX.Element {
		return (
			<div onClick={this.onClick}>
				{this.props.children}
			</div>
		);
	}
}

interface ModalProps extends React.Props<any> {}

export class RepositoryModal extends React.Component<ModalProps, {}> {
	render(): JSX.Element {
		return (
			<div className="modal fade" id={App.REPOSITORY_MODAL_ID} tabIndex={-1} role="dialog">
				<div className="modal-dialog" role="document">
					<div className="modal-content">
						<div className="modal-body">
							<RepositoryForm />
						</div>
					</div>
				</div>
			</div>
		);	
	}
}

export class PackageModal extends React.Component<ModalProps, {}> {
	render(): JSX.Element {
		return (
				<div className="modal fade" id={App.PACKAGE_MODAL_ID} tabIndex={-2} role="dialog">
					<div className="modal-dialog" role="document">
						<div className="modal-content">
							<div className="modal-body">
								<PackageForm />
							</div>
						</div>
					</div>
				</div>
		);
	}
}
