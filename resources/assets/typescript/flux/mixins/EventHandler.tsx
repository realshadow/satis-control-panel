/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import Postal = require('postal');
import ReactMixin = require('react-mixin');

const DEFAULT_CHANNEL = 'react-events';

export interface Interface {
    channel?: IChannelDefinition<any>;
    subscribe: (topic: string, callback: ICallback<any>) => void; 
    publish(topic: string, data: any): void;
}

export var Mixin: Interface = {
    static channel: Postal.channel(DEFAULT_CHANNEL),
    
    subscribe(topic: string, callback: ICallback<any>): void {
        Mixin.channel.subscribe(topic, callback);
    },
    
    publish(topic: string, data: any): void {
        Mixin.channel.publish(topic, data);
    }
};