// Regular Expression for URL validation
//
// http://mathiasbynens.be/demo/url-regex
var urlRegex: RegExp = /((git|ssh|http(s)?)|(git@[\w.]+))(:(\/\/)?)([\w.@:\/\-~]+)(.git)(\/)?/;

export default class Validator {
	static required(value: string, isRequired: any): boolean {
		return (isRequired && value.trim().length > 0);
	}
	
	static url(value: string): boolean {
		return true;
		// return urlRegex.test(value);
	}

	static regex(value: string, regex: RegExp): boolean {
		return regex.test(value);
	}
}
