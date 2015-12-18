import md5 = require('blueimp-md5');

export class App {
	static NOTIFICATION = 'notification';
	static REPOSITORY_FILTER = 'repository.filter';	
	static REPOSITORY_DELETION_FAILED = 'repository.delete.failed';
	static REPOSITORY_ADD = 'repository.add';
	static REPOSITORY_UPDATE = 'repository.update';
	static REPOSITORY_MODAL_ID = 'repositoryForm';
	static PACKAGE_FILTER = 'package.filter';	
	static PACKAGE_DELETION_FAILED = 'package.delete.failed';
	static PACKAGE_ADD = 'package.add';
	static PACKAGE_UPDATE = 'package.update';
	static PACKAGE_MODAL_ID = 'packageForm';
	static ACTIVATE_REPOSITORY_FORM = 'activate.repository.form';
	static ACTIVATE_PACKAGE_FORM = 'activate.package.form';
	static REGISTER_VALIDATION_COMPONENT = 'register.validation.component';
	static FORM_REF_PREFIX = 'field_';
	static TRIGGER_BUILD = 'trigger.satis.build';
	
	static nameToId(repositoryUrl: string) {
        return md5(repositoryUrl);
    }

	static makePackages(require: Object): Array<PackageInterface> {
		let packages: Array<PackageInterface> = [];

		Object.keys(require).forEach((name: string) => {
			let composerPackage = new ComposerPackage(name, require[name]);

			packages.push(composerPackage);
		});

		return packages;
	}
}

export class ComposerPackage implements PackageInterface {
	constructor(public name, public version) {

	}
}
