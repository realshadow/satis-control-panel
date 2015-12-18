/// <reference path="../react/react.d.ts" />

declare module "griddle-react" {
	interface GriddleColumnMetadata {
		columnName: string;
		order?: number;
		locked?: boolean;
		cssClassName?: string;
		displayName?: string;
		customComponent: React.Component<any, any>;
	}
	
	interface GriddleProps {		
		results: Array<Object>;
		columns?: Array<string>;
		columnMetadata?: Array<GriddleColumnMetadata>;
		resultsPerPage?: number;
		initialSort?: string;
		initialSortAscending?: boolean;
		gridClassName?: string;						
		tableClassName?: string;
		customFormatClassName?: string;
		settingsText?: string;
		filterPlaceholderText?: string;
		nextText?: string;
		previousText?: string;
		maxRowsText?: string;
		enableCustomFormatText?: string;
		childrenColumnName?: string;
		metadataColumns?: Array<string>;
		showFilter?: boolean;
		showSettings?: boolean;
		useCustomRowComponent?: boolean;
		useCustomGridComponent?: boolean;
		useCustomPagerComponent?: boolean;
		useGriddleStyles?: boolean;
		customRowComponent?: React.Component<any, any>;
		customGridComponent?: React.Component<any, any>;
		customPagerComponent?: React.Component<any, any>;
		enableToggleCustom?: boolean;
		noDataMessage?: string;
		customNoDataComponent?: React.Component<any, any>;
		showTableHeading?: boolean;
		showPager?: boolean;
		useFixedHeader?: boolean;
		useExternal?: boolean;
		externalSetPage?: Function;
		externalChangeSort?: Function;
		externalSetFilter?: Function;
		externalSetPageSize?: Function;
		externalMaxPage?: number;
		externalCurrentPage?: number;
		externalSortColumn?: string;
		externalSortAscending?: boolean;
		externalLoadingComponent?: React.Component<any, any>;
		externalIsLoading?: boolean;
		enableInfiniteScroll?: boolean;
		bodyHeight?: number;
		paddingHeight?: number;
		rowHeight?: number;
		infiniteScrollLoadTreshold?: number;
		useFixedLayout?: boolean;
		isSubGriddle?: boolean;
		enableSort?: boolean;
		sortAscendingClassName?: string;
		sortDescendingClassName?: string;
		parentRowCollapsedClassName?: string;
		parentRowExpandedClassName?: string;
		settingsToggleClassName?: string;
		nextClassName?: string;
		previousClassName?: string;
		sortAscendingComponent?: string;
		sortDescendingComponent?: string;
		parentRowCollapsedComponent?: string;
		parentRowExpandedComponent?: string;
		settingsIconComponent?: string;
		nextIconComponent?: string;
		previousIconComponent?: string;
		onRowClick?: Function;
	}
	
	interface GriddleComponent<P> {
		new(props?: P, context?: any): React.Component<P, any>;
	}
	
	var Griddle: GriddleComponent<GriddleProps>;

	export = Griddle;
}