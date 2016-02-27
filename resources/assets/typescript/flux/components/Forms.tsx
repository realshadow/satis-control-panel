/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import ReactMixin = require('react-mixin');
import ReactDom = require('react-dom');
import axios = require('axios');

import {App} from '../../helpers';
import EventHandler = require('../mixins/EventHandler');
import {Input, Select} from './FormElements';
import Validator from '../../helpers/Validator';

interface PackageFormProps extends React.Props<any> {}
interface PackageFormFields {
	name: string;
}

interface PackageFormState {
	fields: PackageFormFields;
	isFormValid: boolean;
	isSubmited: boolean;
	repositoryTypes: Array<string>;
}

interface ValidatedComponentInterface {
	rules: Object;
	messages: Object;
	ref: string;
}

@ReactMixin.decorate(EventHandler.Mixin)
class Form extends React.Component<any, any> implements EventHandler.Interface {
	subscribe:(topic:string, callback:ICallback<any>) => void;
	publish:(topic:string, data:any) => void;

	protected validation:Array<ValidatedComponentInterface> = new Array();
	protected validationRules: Object;
	protected validationMessages: Object;

	constructor(props: PackageFormProps) {
		super(props);

		this.submit = this.submit.bind(this);
		this.onKeyPress = this.onKeyPress.bind(this);
		this.fillForm = this.fillForm.bind(this);
		this.validateForm = this.validateForm.bind(this);
		this.registerValidationComponents = this.registerValidationComponents.bind(this);

		this.setInitialState();
	}

	setInitialState(): void {
		throw 'setInitialState method must be defined in child class.';
	}

	submit(event): void {
		throw 'submit method must be defined in child class.';
	}

	fillForm(repository): void {
		throw 'fillForm method must be defined in child class.';
	}

	componentDidMount(): void {
		this.registerValidationComponents();
	}

	getValidationRulesFor(field: string): any {
		return this.validationRules[field];
	}

	getValidationMessagesFor(field: string): any {
		return this.validationMessages[field];
	}

	registerValidationComponents(): void {
		Object.keys(this.refs).forEach((ref: string) => {
			if(ref.lastIndexOf(App.FORM_REF_PREFIX, 0) === 0) {
				let validationRules: Object = this.refs[ref].props.validationRules || {};
				let validationMessages: Object = this.refs[ref].props.validationMessages || {};

				if(validationRules && validationMessages) {
					this.validation.push({
						ref: ref,
						rules: validationRules,
						messages: validationMessages
					});
				}
			}
		});
	}

	onKeyPress(event): void {
		if(event.key === 'Enter') {
			event.preventDefault();

			this.submit(event);
		}
	}

	serialize(): Object {
		let formData: Object = {};

		Object.keys(this.refs).forEach((ref: string) => {
			if(ref.lastIndexOf(App.FORM_REF_PREFIX, 0) === 0) {
				let component = this.refs[ref];

				formData[component.props.name] = component.state.value || component.state.selectedOption ||
						component.props.value;
			}
		});

		return formData;
	}

	validateForm(event): void {
		event.preventDefault();

		let state = this.state;
		let breakOn: string = null;

		if(['keypress', 'blur'].indexOf(event.nativeEvent.type) !== -1) {
			breakOn = App.FORM_REF_PREFIX + event.target.getAttribute('name');
		}

		this.validation.map((component: ValidatedComponentInterface) => {
			if(breakOn && breakOn !== component.ref) return;

			let element: HTMLInputElement = ReactDom.findDOMNode(this.refs[component.ref]).getElementsByClassName('form-control').item(0) as HTMLInputElement;
			let componentState: any = this.refs[component.ref].state;

			let stopLoop: boolean = false;
			Object.keys(component.rules).map((rule: string) => {
				if(stopLoop === true) return;

				let param: any = component.rules[rule];

				if(Validator[rule](element.value, param)) {
					state.isFormValid = true;

					componentState.hasError = false;
					componentState.errorMessage = '';
				} else {
					state.isFormValid = false;

					componentState.hasError = true;
					componentState.errorMessage = component.messages[rule];

					stopLoop = true;
				}
			});

			this.refs[component.ref].setState(componentState);
		});

		this.setState(state);
	}
}

export class RepositoryForm extends Form {
	protected validationRules: Object = {
		type: { required: true },
		url: { required: true, url: true }
	}

	protected validationMessages: Object = {
		type: {
			required: 'Repository type must be provided.'
		},
		url: {
			required: 'Repository url must be provided.',
			url: 'This field is not a valid URL address.'
		}
	}

	setInitialState(): void {
		this.state = {
			fields: {
				url: '',
				type: ''
			},
			isFormValid: true,
			isSubmited: false,
			repositoryTypes: Satis.repository_types
		};
	}

	componentDidMount(): void {
		this.subscribe(App.ACTIVATE_REPOSITORY_FORM, this.fillForm);

		super.componentDidMount();
	}
	
	submit(event): void {
		this.validateForm(event);
		
		if(this.state.isFormValid) {
			let state = this.state;
			
			state.isSubmited = true;
			
			this.setState(state);

			let formData: Object = this.serialize();

			let repositoryId: string = '';
			let action: string = 'added';
			let publishEvent: string = App.REPOSITORY_ADD;

			if(formData['repositoryId']) {
				repositoryId = formData['repositoryId'];
				action = 'updated';
				publishEvent = App.REPOSITORY_UPDATE
			}

			formData['disable_build'] = true;

			delete formData['repositoryId']

			const config: axios.RequestOptions = {
				method: (repositoryId.length > 0 ? 'put' : 'post'),
				url: '/control-panel/api/repository' + (repositoryId.length > 0 ? '/' : '') + repositoryId,
				data: formData
			};

			axios(config)
				.then((response: axios.Response) => {
					if(response.status === 200) {
						this.publish(App.NOTIFICATION, {
							message: 'Repository was successfully ' + action
						});
						
						let event = new MouseEvent('click', {
							'view': window, 
							'bubbles': true, 
							'cancelable': false
						});

						let node = document.querySelector('#' + App.REPOSITORY_MODAL_ID + ' #close-modal');
						node.dispatchEvent(event);
						
						this.publish(publishEvent, {
							repository: formData,
							repositoryId: repositoryId
						});

						state.isSubmited = false;

						this.setState(state);
					}
				})
				.catch((response: axios.Response) => {
					this.publish(App.NOTIFICATION, {
						message: 'Encountered unexpected error. Check logs for details.',
						level: 'error'
					});

					state.isSubmited = false;

					this.setState(state);
				});
		}
	}
	
	fillForm(repository: RepositoryInterface): void {
		this.refs[App.FORM_REF_PREFIX + 'type'].setState({
			selectedOption: (typeof repository.type === 'undefined' ? 'vcs' : repository.type),
			hasError: null,
			errorMessage: ''
		});
		
		this.refs[App.FORM_REF_PREFIX + 'url'].setState({
			value: repository.url,
			hasError: null,
			errorMessage: ''
		});

		this.refs[App.FORM_REF_PREFIX + 'repositoryId'].setState({
			value: (repository.url ? App.nameToId(repository.url) : null),
			hasError: null,
			errorMessage: ''
		});

		let state: PackageFormState = this.state;
		
		state.isFormValid = true;
		
		this.setState(state);
	}
	
	render(): JSX.Element {
		let defaultRepositoryType: string = this.state.fields.type || '';

		return (
			<form name="repository" method="post">
				<fieldset>
					<Select 
						ref={App.FORM_REF_PREFIX + 'type'}
						name="type"
						id="type"
						label="Repository type"
						selectedOption={defaultRepositoryType}
						options={this.state.repositoryTypes}
						validationRules={this.getValidationRulesFor('type')}
						validationMessages={this.getValidationMessagesFor('type')}						
					/>
					<Input 
						ref={App.FORM_REF_PREFIX + 'url'}
						name="url"
						type="text"
						id="url"
						label="Repository URL"
						value={this.state.fields.url || null}
						onBlur={this.validateForm}
						onKeyPress={this.onKeyPress}
						validationRules={this.getValidationRulesFor('url')}
						validationMessages={this.getValidationMessagesFor('url')}
					/>
					<Input
						ref={App.FORM_REF_PREFIX + 'repositoryId'}
						name="repositoryId"
						type="hidden"
						value={this.state.fields.url || null}
						onKeyPress={this.onKeyPress}
					/>
					<div className="modal-footer">
						<button type="button" className="btn btn-default" id="close-modal" data-dismiss="modal">Close</button>
						<button type="button" 
							className={"btn btn-success" + (this.state.isSubmited ? ' disabled' : '')} 
							onClick={this.submit}
							disabled={this.state.isSubmited}
						>{this.state.isSubmited ? 'Saving repository...' : 'Save repository'}</button>
					</div>				
				</fieldset>
			</form>		
		);		
	}
}

export class PackageForm extends Form {
	protected validationRules: Object = {
		name: {
			required: true,
			regex: /[A-Za-z0-9][A-Za-z0-9_.-]*\/[A-Za-z0-9][A-Za-z0-9_.-]*/
		}
	}

	protected validationMessages: Object = {
		name: {
			required: 'Package name must be provided.',
			regex: 'Package name must be in valid format. E.g. organization/package'
		}
	}

	setInitialState(): void {
		this.state = {
			fields: {
				name: ''
			},
			isFormValid: true,
			isSubmited: false
		};
	}

	componentDidMount(): void {
		this.subscribe(App.ACTIVATE_PACKAGE_FORM, this.fillForm);

		super.componentDidMount();
	}

	submit(event): void {
		this.validateForm(event);

		if(this.state.isFormValid) {
			let state = this.state;

			state.isSubmited = true;

			this.setState(state);

			let formData: Object = this.serialize();
			formData['name'] = formData['name'].trim();

			let packageId: string = '';
			let action: string = 'added';
			let publishEvent: string = App.PACKAGE_ADD;

			if(formData['packageId']) {
				packageId = formData['packageId'];
				action = 'updated';
				publishEvent = App.PACKAGE_UPDATE;
			}

			delete formData['packageId'];

			formData['disable_build'] = true;

			const config: axios.RequestOptions = {
				method: (packageId.length > 0 ? 'put' : 'post'),
				url: '/control-panel/api/package' + (packageId.length > 0 ? '/' : '') + packageId,
				data: formData
			};

			axios(config)
				.then((response: axios.Response) => {
					if(response.status === 200) {
						this.publish(App.NOTIFICATION, {
							message: 'Package was successfully ' + action
						});

						let event = new MouseEvent('click', {
							'view': window,
							'bubbles': true,
							'cancelable': false
						});

						let node = document.querySelector('#' + App.PACKAGE_MODAL_ID + ' #close-modal');
						node.dispatchEvent(event);

						this.publish(publishEvent, {
							'package': formData,
							packageId: packageId
						});

						state.isSubmited = false;

						this.setState(state);
					}
				});
		}
	}

	fillForm(composerPackage: PackageInterface): void {
		this.refs[App.FORM_REF_PREFIX + 'name'].setState({
			value: composerPackage.name,
			hasError: null,
			errorMessage: ''
		});

		this.refs[App.FORM_REF_PREFIX + 'version'].setState({
			value: (composerPackage.version ? composerPackage.version : null),
			hasError: null,
			errorMessage: ''
		});		
		
		this.refs[App.FORM_REF_PREFIX + 'packageId'].setState({
			value: (composerPackage.name ? App.nameToId(composerPackage.name) : null),
			hasError: null,
			errorMessage: ''
		});

		let state: PackageFormState = this.state;

		state.isFormValid = true;

		this.setState(state);
	}

	render(): JSX.Element {
		return (
			<form name="repository" method="post">
				<fieldset>
					<Input
						ref={App.FORM_REF_PREFIX + 'name'}
						name="name"
						type="text"
						id="name"
						label="Package name"
						value={this.state.fields.name || null}
						onBlur={this.validateForm}
						onKeyPress={this.onKeyPress}
						validationRules={this.getValidationRulesFor('name')}
						validationMessages={this.getValidationMessagesFor('name')}
					/>
					<Input
						ref={App.FORM_REF_PREFIX + 'version'}
						name="version"
						type="hidden"
						value={this.state.fields.version || '*'}
						onKeyPress={this.onKeyPress}
					/>
					<Input
						ref={App.FORM_REF_PREFIX + 'packageId'}
						name="packageId"
						type="hidden"
						value={this.state.fields.name || null}
						onKeyPress={this.onKeyPress}
					/>
					<div className="modal-footer">
						<button type="button" className="btn btn-default" id="close-modal" data-dismiss="modal">Close</button>
						<button type="button"
						        className={"btn btn-success" + (this.state.isSubmited ? ' disabled' : '')}
						        onClick={this.submit}
						        disabled={this.state.isSubmited}
						>{this.state.isSubmited ? 'Saving package...' : 'Save package'}</button>
					</div>
				</fieldset>
			</form>
		);
	}
}
