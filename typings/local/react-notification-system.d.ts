/// <reference path="../react/react.d.ts" />

interface NotificationSystemMessage {
	message: string;
	title?: string;		
	level?: string;
	position?: string;
	autoDismiss?: number;
	dismissible?: boolean;
	action?: Object;
	onAdd?: Function;
	onRemove?: Function;
	uid?: number|string;		
}

interface NotificationSystemProps {
	ref: string;
	noAnimation?: boolean;
}

interface NotificationSystemInterface<P> {
	new(props?: P, context?: any): React.Component<P, any>;
	
	addNotification: (notification: NotificationSystemMessage) => void;
	removeNotification: (notification: NotificationSystemMessage) => void;		
}

declare var NotifactionSystem: NotificationSystemInterface<NotificationSystemProps>;

declare module "react-notification-system" {
	var NotifactionSystem: NotificationSystemInterface<NotificationSystemProps>;
	
	export = NotifactionSystem;
}
