/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');

interface InfoPanelProps extends React.Props<any> {
	name: string;
	homepage: string;
}

export class InfoPanel extends React.Component<InfoPanelProps, {}> {
	render(): JSX.Element {
		return (
			<div className="panel-heading">
				<span className="nav-tab-title pull-left">
					<i className="fa fa-home"></i>&nbsp;
					<a href={this.props.homepage} className="logo active" title={this.props.name}>{this.props.name || ':('}</a>
				</span>
				<ul className="nav nav-tabs">
					<li className="active"><a href="#repositories" data-toggle="tab">Private packages</a></li>
					<li className=""><a href="#packages" data-toggle="tab">Packagist cache</a></li>
				</ul>
			</div>
		);
	}
}
