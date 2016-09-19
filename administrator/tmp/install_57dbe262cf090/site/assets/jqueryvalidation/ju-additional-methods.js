jQuery.validator.addMethod("maxFile", function(value, element, param) {
	// param = size (en bytes)
	// element = element to validate (<input>)
	// value = value of the element (file name)
	return this.optional(element) || element.files.length > param;

}, jQuery.validator.format("Please enter less than or equal to {0} Files."));

jQuery.validator.addMethod("minFile", function(value, element, param) {
	// param = size (en bytes)
	// element = element to validate (<input>)
	// value = value of the element (file name)
	return this.optional(element) || element.files.length < param;

}, jQuery.validator.format("Please enter greater than or equal to {0} Files."));

jQuery.validator.addMethod("maxFileSize", function(value, element, param) {
	// param = size (en bytes)
	// element = element to validate (<input>)
	// value = value of the element (file name)
	if(this.optional(element)){
		return true;
	}

	for(x in element.files){
		if(element.files.hasOwnProperty(x)){
			if(element.files[x].size > parseFloat(param) * 1048576){
				return false;
			}
		}
	}

	return true;

}, jQuery.validator.format("Please enter a file size less than or equal to {0} MB."));

jQuery.validator.addMethod("minFileSize", function(value, element, param) {
	// param = size (en bytes)
	// element = element to validate (<input>)
	// value = value of the element (file name)
	if(this.optional(element)){
		return true;
	}

	for(x in element.files){
		if(element.files.hasOwnProperty(x)){
			if(element.files[x].size < parseFloat(param) * 1048576){
				return false;
			}
		}
	}

	return true;

}, jQuery.validator.format("Please enter a file size greater than or equal to {0} MB."));

jQuery.validator.addMethod("totalMaxFileSize", function(value, element, param) {
	// param = size (en bytes)
	// element = element to validate (<input>)
	// value = value of the element (file name)
	if(this.optional(element)){
		return true;
	}

	$totalSize = 0;
	for(x in element.files){
		if(element.files.hasOwnProperty(x)){
			$totalSize = parseInt($totalSize) + parseInt(element.files[x].size);
		}
	}

	if($totalSize > parseFloat(param) * 1048576){
		return false;
	}

	return true;

}, jQuery.validator.format("Please enter total files size less than or equal to {0} MB."));

jQuery.validator.addMethod("totalMinFileSize", function(value, element, param) {
	// param = size (en bytes)
	// element = element to validate (<input>)
	// value = value of the element (file name)
	if(this.optional(element)){
		return true;
	}

	$totalSize = 0;
	for(x in element.files){
		if(element.files.hasOwnProperty(x)){
			$totalSize = parseInt($totalSize) + parseInt(element.files[x].size);
		}
	}

	if($totalSize < parseFloat(param) * 1048576){
		return false;
	}

	return true;

}, jQuery.validator.format("Please enter total files size greater than or equal to {0} MB."));
