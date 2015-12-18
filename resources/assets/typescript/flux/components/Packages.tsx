/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import ReactMixin = require('react-mixin');
import axios = require('axios');

import {App} from '../../helpers';
import {ModalTrigger} from './Modal';
import EventHandler = require('../mixins/EventHandler');

interface PackagesInterface {
    packages: Array<PackageInterface>;
}

interface PackageEventDataInterface {
    package:  PackageInterface;
    packageId: string;
}

interface PackagesProps extends React.Props<any>, PackagesInterface {}
interface PackagesState extends PackagesInterface {
    initialPackageState: Array<PackageInterface>;
    filterActive: boolean;    
}

interface PackageState {
    onDeletePackage: (event) => void;
    isBeingDeleted?: boolean;
}

interface PackageProps extends React.Props<any>, PackageInterface, PackageState {}

@ReactMixin.decorate(EventHandler.Mixin)
class Package extends React.Component<PackageProps, PackageState> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void; 
    publish: (topic: string, data: any) => void;

    constructor(props: PackageProps) {
        super(props);

        this.build = this.build.bind(this);
        this.delete = this.delete.bind(this);
        this.deleteFailed = this.deleteFailed.bind(this);
        
        this.state = {
            onDeletePackage: this.props.onDeletePackage,
            isBeingDeleted: false
        };
    }

    build(event): void {
        this.publish(App.TRIGGER_BUILD, {
            item: event.currentTarget.getAttribute('value'),
            type: 'public'
        });
    }

    delete(event): void {
        let state = this.state;
        
        this.state.onDeletePackage(event.currentTarget.getAttribute('value'));
        
        state.isBeingDeleted = true;
        
        this.setState(state);
    }
    
    deleteFailed(repositoryUrl: string): void {
        if(this.props.name === repositoryUrl) {
            let state = this.state;

            state.isBeingDeleted = false;

            this.setState(state);
        }
    }

    componentDidMount(): void {
        this.subscribe(App.PACKAGE_DELETION_FAILED, this.deleteFailed);
    }

    render(): JSX.Element {
        return (
            <li className={this.state.isBeingDeleted === true ? "deleting-phase" : ""}>
                <span className="value">
                    <a href={this.props.name} target="_blank" title={this.props.name}>{this.props.name}</a>
                    <label>({this.props.version.toLowerCase()})</label>
                </span>
                <ModalTrigger
                    activeForm={App.ACTIVATE_PACKAGE_FORM}
                    packageName={this.props.name}
                    packageVersion={this.props.version}>
                    <span className="label label-primary"
                        data-toggle="modal"
                        data-target={'#' + App.PACKAGE_MODAL_ID}
                    >
                        <i className="fa fa-pencil"></i> Edit
                    </span>
                </ModalTrigger>
                <span onClick={this.state.isBeingDeleted === false ? this.delete : null} value={this.props.name}
                    className="label label-danger"><i className="fa fa-remove"></i> Delete</span>
                <span onClick={this.build} value={this.props.name}
                    className="label label-warning"><i className="fa fa-repeat"></i> Build</span>
            </li>
        );
    }
}

@ReactMixin.decorate(EventHandler.Mixin)
export class Packages extends React.Component<PackagesProps, PackagesState> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void;
    publish: (topic: string, data: any) => void;

    constructor(props: PackagesProps) {
        super(props);

        this.applyFilter = this.applyFilter.bind(this);
        this.onDeletePackage = this.onDeletePackage.bind(this);
        this.onAddPackage = this.onAddPackage.bind(this);
        this.onUpdatePackage = this.onUpdatePackage.bind(this);

        this.state = {
            packages: this.props.packages,
            initialPackageState: this.props.packages,
            filterActive: false
        };
    }

    onAddPackage(data: PackageEventDataInterface): void {
        let state: PackagesState = this.state;

        state.initialPackageState.push(data.package);
        state.packages.push(data.package);

        this.setState(state);
    }

    onUpdatePackage(data: PackageEventDataInterface): void {
        let state: PackagesState = this.state;

        ['initialPackageState', 'packages'].forEach((property: any) => {
            state[property] = state[property].map((composerPackage: PackageInterface) => {
                if(data.packageId === App.nameToId(composerPackage.name)) {
                    composerPackage = data.package;
                }

                return composerPackage;
            });
        });

        this.setState(state);
    }

    onDeletePackage(packageId: string): void {
        let state: PackagesState = this.state;

		axios.delete('/control-panel/api/package/' + App.nameToId(packageId), { disable_build: true })
			.then((response: axios.Response) => {
                if(response.status === 200) {
                    this.publish(App.NOTIFICATION, {
                        message: 'Package was successfully deleted',
                        level: 'success'
                    });

                    ['initialPackageState', 'packages'].forEach((property: any) => {
                        state[property] = state[property].filter((composerPackage: PackageInterface) => {
                            return packageId !== composerPackage.name;
                        });
                    });

                    this.setState(state);
                }
			})
			.catch((response: axios.Response) => {
                this.publish(App.NOTIFICATION, {
                    message: 'Encountered unexpected error when deleting package. Check logs for details.',
                    level: 'error'
                });

                this.publish(App.PACKAGE_DELETION_FAILED, packageId);
			});                
    }
    
    applyFilter(filterValue: string, envelope: IEnvelope<any>): void {
        let state: PackagesState = this.state;        
        
        state.filterActive = filterValue.trim().length > 0;
        
        state.packages = this.state.initialPackageState.filter((repository: PackageInterface) => {
            let match = repository.name.toLowerCase().indexOf(filterValue.toLowerCase());
            
            return (match !== -1);
        });            
        
        this.setState(state);
    }    
       
    componentDidMount(): void {
       this.subscribe(App.PACKAGE_FILTER, this.applyFilter);
       this.subscribe(App.PACKAGE_ADD, this.onAddPackage);
       this.subscribe(App.PACKAGE_UPDATE, this.onUpdatePackage);
    }    
    
	render(): JSX.Element {
        let packages: Array<PackageInterface> = this.state.packages || this.props.packages;                       
        
        let output = null;
        if(this.state.packages.length > 0) {
            output = (
                <ul>
                    {
                        packages.map((repository: PackageInterface, index: number) => {
                            return <Package 
                                        onDeletePackage={this.onDeletePackage} 
                                        key={App.nameToId(repository.name)}
                                        {...repository} 
                                    />                        
                        })
                    }
                </ul>                
            )
        } else {
            output = ( 'No packages were found.' );
        }
        
		return (
            <div className="col-md-12 panel-packages">            
                {output}
            </div>
        );
    }     
}