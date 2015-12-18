interface RepositoryInterface {
	type: string;
	url: string;	    
}

interface PackageInterface {
	name: string;
	version: string;
}

interface SatisConfigInterface {
	name: string;
	homepage: string;
	repositories: Array<RepositoryInterface>;
	require: Object;
}

interface SatisNodeServerInterface {
	host: string;
	port: number;
	lock_file: string;
}

interface SatisInterface {
	loaded: boolean;
	config: SatisConfigInterface;
	message: string;
	locked: boolean;
	repository_types: Array<string>;
	node_server: SatisNodeServerInterface;
}

interface BuildContextInterface {
	type: string;
	item: string;
}

declare var Satis: SatisInterface;
declare var io: any;