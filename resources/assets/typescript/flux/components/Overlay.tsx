/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');

interface OverlayState {
	show: boolean;
	onLock: Function;
	onUnlock: Function;
}

interface OverlayProps extends React.Props<any>, OverlayState {
    notify: Function;
}

export class Overlay extends React.Component<OverlayProps, OverlayState> {
	constructor(props: OverlayProps) {
		super(props);
		
		this.state = {
			show: this.props.show,
			onLock: this.props.onLock,
			onUnlock: this.props.onUnlock
		};
	}
	
	onLock(): void {
		this.state.onLock();		
	}
	
	onUnlock(): void {
		this.state.onUnlock();
	}
	
	render(): JSX.Element {
		return (
			<div className={"overlay" + (this.state.show === true ? " active" : "")}>
				<img src="/control-panel/images/preloader.gif" width="48" height="48" />
			</div>
		);
	}
}
