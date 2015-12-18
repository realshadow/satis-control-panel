/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import ReactMixin = require('react-mixin');
import ReactDom = require('react-dom');
import Dropzone = require('react-dropzone');

import {App} from '../../helpers';
import {ModalTrigger} from './Modal';
import {Input} from './FormElements';
import EventHandler = require('../mixins/EventHandler');
import FormEventHandler = __React.FormEventHandler;

//&nbsp;<a href="/create" className="btn btn-primary btn-sm pull-right"><i className="fa fa-plus"></i> Import from file</a>

class ImportLockFile extends React.Component<{}, {}> {
	onDrop(files): void {
		console.log('Received files: ', files);
	}

	render() {
		return (
            <div>
                <Dropzone onDrop={this.onDrop}>
                    <div>Try dropping some files here, or click to select files to upload.</div>
                </Dropzone>
            </div>
		);
	}
}

interface ActionPanelProps extends React.Props<any> {
	placeholder: string;
	button: string;
	modalId: string;
	onFilter: FormEventHandler;
	activateForm: string;
	enableBuild?: boolean;
	buildContext: string;
}

@ReactMixin.decorate(EventHandler.Mixin)
export class ActionPanel extends React.Component<ActionPanelProps, {}> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void; 
	publish: (topic: string, data: any) => void;
	
	constructor(props: ActionPanelProps) {
		super(props);
		
		this.build = this.build.bind(this);

		this.state = {};
	}
	
	build(event): void {
		this.publish(App.TRIGGER_BUILD, {
			item: null,
			type: this.props.buildContext
		});
	}

	onKeyPress(event): void {
		if(event.key === 'Enter') {
			event.preventDefault();
		}
	}

	render(): JSX.Element {
		return (
			<div className="col-md-12 panel-actions">
				<form className="form-horizontal col-md-6">
					<fieldset>
						<div className="col-md-5">
							<Input 
								name={this.props.button.replace(' ', '_').toLowerCase()}
								type="text"
								placeholder={this.props.placeholder}
								onChange={this.props.onFilter}
							    onKeyPress={this.onKeyPress}
							/>
						</div>		
					</fieldset>
				</form>
				
				<div className="col-md-6 text-right">
					{ this.props.enableBuild ? (
						<a onClick={this.build} className="btn btn-primary btn-sm pull-right">
							<i className="fa fa-repeat"></i>&nbsp; Build
						</a>
					) : '' }
					<ModalTrigger activeForm={this.props.activateForm}>
						<a className="btn btn-success btn-sm pull-right"
							data-toggle="modal" data-target={'#' + this.props.modalId}
						>
							<i className="fa fa-plus"></i> {this.props.button}
						</a>
					</ModalTrigger>
				</div>
			</div>
		);	
	}
}
