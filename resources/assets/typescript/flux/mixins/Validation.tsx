/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import Postal = require('postal');
import ReactMixin = require('react-mixin');

import {App} from '../../helpers';

interface ValidationRulesInterface {
	required?: boolean;
	url?: boolean;
}

export interface ComponentInterface {
	registerValidationComponent?: ICallback<any>;

}

export interface Interface {
    componentDidMount: () => void; 
}

export var Mixin: Interface = {
    componentDidMount(): void {
		if(this.props.validationRules) {
			this.publish(App.REGISTER_VALIDATION_COMPONENT, {
				ref: App.FORM_REF_PREFIX + this.props.name,
				rules: this.props.validationRules,
				messages: this.props.validationMessages
			});			
		}
	}
};