/// <reference path="../../../../../typings/tsd.d.ts"/>

import React = require('react');
import ReactMixin = require('react-mixin');
import axios = require('axios');

import {App} from '../../helpers';
import {ModalTrigger} from './Modal';
import EventHandler = require('../mixins/EventHandler');

interface RepositoriesInterface {
    repositories: Array<RepositoryInterface>;
}

interface RepositoryEventDataInterface {
   repository:  RepositoryInterface;
   repositoryId: string;
}

interface RepositoriesProps extends React.Props<any>, RepositoriesInterface {}
interface RepositoriesState extends RepositoriesInterface {
    initialRepositoryState: Array<RepositoryInterface>;
    filterActive: boolean;    
}

interface RepositoryState {
    onDeleteRepository: (event) => void;
    isBeingDeleted?: boolean;
}

interface RepositoryProps extends React.Props<any>, RepositoryInterface, RepositoryState {}

@ReactMixin.decorate(EventHandler.Mixin)
class Repository extends React.Component<RepositoryProps, RepositoryState> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void; 
    publish: (topic: string, data: any) => void;
    
    constructor(props: RepositoryProps) {
        super(props);

        this.build = this.build.bind(this);
        this.delete = this.delete.bind(this);
        this.deleteFailed = this.deleteFailed.bind(this);
        
        this.state = {
            onDeleteRepository: this.props.onDeleteRepository,
            isBeingDeleted: false
        };
    }

    build(event): void {
        this.publish(App.TRIGGER_BUILD, {
            item: event.currentTarget.getAttribute('value'),
            type: 'private'
        });
    }
    
    delete(event): void {
        let state = this.state;
        
        this.state.onDeleteRepository(event.currentTarget.getAttribute('value'));
        
        state.isBeingDeleted = true;
        
        this.setState(state);
    }
    
    deleteFailed(repositoryUrl: string): void {
        if(this.props.url === repositoryUrl) {
            let state = this.state;
            
            state.isBeingDeleted = false;
            
            this.setState(state);            
        }
    }
    
    componentDidMount(): void {
        this.subscribe(App.REPOSITORY_DELETION_FAILED, this.deleteFailed);
    }
    
    render(): JSX.Element {
        return (
            <li className={this.state.isBeingDeleted === true ? "deleting-phase" : ""}>                                 
                <span className="value">
                    <a href={this.props.url} target="_blank" title={this.props.url}>{this.props.url}</a>
                    <label>({this.props.type.toLowerCase()})</label>
                </span>
                <ModalTrigger activeForm={App.ACTIVATE_REPOSITORY_FORM} repositoryUrl={this.props.url} repositoryType={this.props.type}>
                    <span className="label label-primary"
                        data-toggle="modal"
                        data-target={'#' + App.REPOSITORY_MODAL_ID}
                    >
                        <i className="fa fa-pencil"></i> Edit
                    </span>
                </ModalTrigger>
                <span onClick={this.state.isBeingDeleted === false ? this.delete : null} value={this.props.url} 
                    className="label label-danger"><i className="fa fa-remove"></i> Delete</span>
                <span onClick={this.build} value={this.props.url}
                    className="label label-warning"><i className="fa fa-repeat"></i> Build</span>
            </li>
        );
    }
}

@ReactMixin.decorate(EventHandler.Mixin)
export class Repositories extends React.Component<RepositoriesProps, RepositoriesState> implements EventHandler.Interface {
    subscribe: (topic: string, callback: ICallback<any>) => void; 
    publish: (topic: string, data: any) => void;
    
    constructor(props: RepositoriesProps) {
        super(props);
        
        this.applyFilter = this.applyFilter.bind(this);
        this.onDeleteRepository = this.onDeleteRepository.bind(this);
        this.onAddRepository = this.onAddRepository.bind(this);
        this.onUpdateRepository = this.onUpdateRepository.bind(this);
        
        this.state = {
            repositories: this.props.repositories,
            initialRepositoryState: this.props.repositories,
            filterActive: false
        };
    }
    
    onAddRepository(data: RepositoryEventDataInterface): void {
        let state: RepositoriesState = this.state;
        
        state.initialRepositoryState.push(data.repository);
        state.repositories.push(data.repository);
        
        this.setState(state);          
    }
    
    onUpdateRepository(data: RepositoryEventDataInterface): void {
        let state: RepositoriesState = this.state;
        
        ['initialRepositoryState', 'repositories'].forEach((property: any) => {
            state[property] = state[property].map((repository: RepositoryInterface) => {
                if(data.repositoryId === App.nameToId(repository.url)) {
                    repository = data.repository;
                }
                
                return repository;
            });
        });        
        
        this.setState(state);          
    }    
    
    onDeleteRepository(repositoryUrl: string): void {
        let state: RepositoriesState = this.state;
        
		axios.delete('/control-panel/api/repository/' + App.nameToId(repositoryUrl), { disable_build: true })
			.then((response: axios.Response) => {
                if(response.status === 200) {
                    this.publish(App.NOTIFICATION, {
                        message: 'Repository was successfully deleted',
                        level: 'success'
                    });
                    
                    ['initialRepositoryState', 'repositories'].forEach((property: any) => {
                        state[property] = state[property].filter((repository: RepositoryInterface) => {
                            return repositoryUrl !== repository.url;
                        });
                    });
                    
                    this.setState(state);
                }
			})
			.catch((response: axios.Response) => {
                this.publish(App.NOTIFICATION, {
                    message: 'Encountered unexpected error when deleting repository. Check logs for details.',
                    level: 'error'
                });
                
                this.publish(App.REPOSITORY_DELETION_FAILED, repositoryUrl);
			});                
    }
    
    applyFilter(filterValue: string, envelope: IEnvelope<any>): void {
        let state: RepositoriesState = this.state;

        state.filterActive = filterValue.trim().length > 0;
        
        state.repositories = this.state.initialRepositoryState.filter((repository: RepositoryInterface) => {
            let match = repository.url.toLowerCase().indexOf(filterValue.toLowerCase());
            
            return (match !== -1);
        });            
        
        this.setState(state);
    }    
       
    componentDidMount(): void {
       this.subscribe(App.REPOSITORY_FILTER, this.applyFilter);
       this.subscribe(App.REPOSITORY_ADD, this.onAddRepository);
       this.subscribe(App.REPOSITORY_UPDATE, this.onUpdateRepository);
    }    
    
	render(): JSX.Element {
        let repositories: Array<RepositoryInterface> = this.state.repositories || this.props.repositories;                       
        
        let output = null;
        if(this.state.repositories.length > 0) {
            output = (
                <ul>
                    {
                        repositories.map((repository: RepositoryInterface, index: number) => {
                            return <Repository 
                                        onDeleteRepository={this.onDeleteRepository} 
                                        key={App.nameToId(repository.url)}
                                        {...repository} 
                                    />                        
                        })
                    }
                </ul>                
            )
        } else {
            output = ( 'No repositories were found.' );
        }
        
		return (
            <div className="col-md-12 panel-repositories">            
                {output}
            </div>
        );
    }     
}