/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import ReactMixin = require('react-mixin');
import ReactDom = require('react-dom');

import {App} from '../../helpers';
import {ModalTrigger} from './Modal';
import EventHandler = require('../mixins/EventHandler');
import Validation = require('../mixins/Validation');

interface ValidationRulesInterface {
	required?: boolean;
	url?: boolean;
}

interface ValidationInterface {
	validationRules?: ValidationRulesInterface;
	validationMessages?: any;
}

interface InputProps extends ValidationInterface, React.Props<any> {
	name: string;
	type: string;
	id?: string;
	value?: string;
	label?: string;
	placeholder?: string;
	className?: string;
	onChange?: React.FormEventHandler;
	onBlur?: React.FocusEventHandler;
	onKeyPress?: React.KeyboardEventHandler;
}

interface InputState {
	value: string;
	hasError: boolean;
	errorMessage: string;
}

@ReactMixin.decorate(EventHandler.Mixin)
export class Input extends React.Component<InputProps, InputState> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void; 
    publish: (topic: string, data: any) => void;
	
	constructor(props: InputProps) {
		super(props);
		
		this.onChange = this.onChange.bind(this);
		
		this.state = {
			value: '',
			hasError: null,
			errorMessage: ''
		}
	}
	
	getErrorClass(): string {
		let className: string = '';
		
		if(this.state.hasError === true) {
			className = ' has-error';
		} else if(this.state.hasError === false) {
			className = ' has-success';
		}
		
		return className;
	}
	
	onChange(event): void {
		let state: InputState = this.state;
		
		state.value = event.currentTarget.value;
		
		this.setState(state);
	}
	
	render(): JSX.Element {
		return (
			<div className={"form-group" + this.getErrorClass()}>
				{(this.props.label ? <label htmlFor="url" className="control-label">{this.props.label}</label> : '')}
				<input id={this.props.id} 
					value={this.state.value || this.props.value} 
					onChange={this.props.onChange || this.onChange}
					onBlur={this.props.onBlur}
					onKeyPress={this.props.onKeyPress}
					name={this.props.name} 
					type={this.props.type} 
					placeholder={this.props.placeholder || this.props.label}
					className={this.props.className || "form-control input-sm"} 
				/>
				<span className="help-block">{this.state.errorMessage}</span>
			</div>					
		);	
	}
}

interface SelectProps extends ValidationInterface, React.Props<any> {
	id: string;
	name: string;
	options: any;
	selectedOption?: string;
	label?: string;
	className?: string;
	onChange?: React.FormEventHandler;
}

interface SelectState {
	selectedOption: string;
	hasError: boolean;
	errorMessage: string;
}

@ReactMixin.decorate(EventHandler.Mixin)
export class Select extends React.Component<SelectProps, SelectState> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void; 
    publish: (topic: string, data: any) => void;
	
	constructor(props: SelectProps) {
		super(props);
		
		this.onChange = this.onChange.bind(this);
		
		this.state = {
			selectedOption: this.props.selectedOption,
			hasError: null,
			errorMessage: ''
		}
	}
	
	getErrorClass(): string {
		let className: string = '';
		
		if(this.state.hasError === true) {
			className = ' has-error';
		} else if(this.state.hasError === false) {
			className = ' has-success';
		}
		
		return className;
	}
	
	onChange(event): void {
		let state: SelectState = this.state;
		
		state.selectedOption = event.target.value;
		
		this.setState(state);
	}		
	
	render(): JSX.Element {
		let options = this.props.options || new Array();

		return (
			<div className={"form-group" + this.getErrorClass()}>
				{(this.props.label ? <label htmlFor="url" className="control-label">{this.props.label}</label> : '')}
				<select id={this.props.id} 
					className={this.props.className || "form-control"} 
					value={this.state.selectedOption}
					onChange={this.props.onChange || this.onChange}
				>
				{
					options.map((option: string, index: number) => {
						return (
							<option key={"option" + index.toString()} value={option.toLowerCase()}>
								{option}
							</option>
						);
					})
				}
				</select>
				<span className="help-block">{this.state.errorMessage}</span>
			</div>				
		);	
	}
}