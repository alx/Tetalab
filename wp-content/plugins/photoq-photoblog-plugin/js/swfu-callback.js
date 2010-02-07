function fileQueueError(file, error_code, message) {
	try {
		var error_name = "";

		switch (error_code) {
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			error_name = swfuCallbackL10n.file + " " + file.name + " " + swfuCallbackL10n.isZero;
			break;
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			error_name = swfuCallbackL10n.file + " " + file.name + swfuCallbackL10n.exceed + " " + this.getSetting('file_size_limit') + swfuCallbackL10n.ini;
			break;
		case SWFUpload.ERROR_CODE_QUEUE_LIMIT_EXCEEDED:
			error_name = swfuCallbackL10n.tooMany;
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			error_name = swfuCallbackL10n.file + " " + file.name + " " + swfuCallbackL10n.invType;
			break;
		default:
			error_name = message;
			break;
		}

		alert(error_name);
		
	} catch (ex) {
		this.debug(ex);
	}

}


function fileQueued(file) {
	try {
		uplsize += Math.round(file.size/1024);
		
		if ( jQuery("#SWFUploadFileListingFiles ul").length == 0 ){
			jQuery("#SWFUploadFileListingFiles").append("<h4 id='queueinfo' class='thead'>"+swfuCallbackL10n.queueEmpty+"</h4><ul></ul>");
		}
		jQuery("#SWFUploadFileListingFiles")
			.children("ul:first")
				.append("<li id='" + file.id + "' class='SWFUploadFileItem'>" + file.name +"<a id='" + file.id + "deletebtn' class='cancelbtn' href='javascript:swfu.cancelUpload(\"" + file.id + "\");'><!-- IE --></a><span class='progressBar' id='" + file.id + "progress'></span></li>")
				.children("li:last").slideDown("slow")
			.end();
	} catch (ex) {
		this.debug(ex);
	}
	
}

function fileDialogComplete(queuelength) {
	try {
		if (queuelength > 0) {
			jQuery("#queueinfo").text(queuelength + " " + swfuCallbackL10n.queued + "( " + uplsize + "KB )");
	
			jQuery("#" + swfu.movieName + "UploadBtn").css("display", "inline");
			jQuery(".browsebtn").text(swfuCallbackL10n.addMore);
			jQuery("#ftpUploadBtn").css("display", "none");
			//start auto upload
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}	
}


function uploadFileCancelled(file, queuelength) {
	
	
}

function uploadStart(file) {
	try{
		jQuery("#queueinfo").text(swfuCallbackL10n.uploading + " " + file.name);
		jQuery("#" + file.id).addClass("fileUploading");
	} catch (ex) {
		this.debug(ex);
	}
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try{
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 350)
		jQuery("#" + file.id + "progress").css("background", "url("+swfuCallbackL10n.progressBarUrl+") no-repeat -" + (350-percent) + "px");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, error_code, message) {
	try {
		switch (error_code) {
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			try {
				jQuery("#"+file.id).text(file.name + " - " + swfuCallbackL10n.cancelled)
					.attr("class","SWFUploadFileItem uploadCancelled")
						.slideUp("fast",function(){
			   				jQuery(this).remove();
			 			});
				uplsize -= Math.round(file.size/1024);
				jQuery("#queueinfo").text(this.getStats().files_queued + " " + swfuCallbackL10n.queued + "( " + uplsize + "KB )");
				if(!this.getStats().files_queued){
					jQuery("#" + swfu.movieName + "UploadBtn").css("display","none");
					jQuery("#SWFUploadFileListingFiles").empty();
					jQuery(".browsebtn").text(swfuCallbackL10n.select);
				}
			}
			catch (ex1) {
				this.debug(ex1);
			}
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			this.debug("Upload stopped: File name: " + file.name);
			break;
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			alert("Upload Error: " + message);
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			alert("Upload Failed.");
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			alert("Server (IO) Error");
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			alert("Security Error");
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			alert("Upload limit exceeded.");
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			alert("Failed Validation.  Upload skipped.");
			this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			alert(message);
			break;
		}

	} catch (ex3) {
		this.debug(ex3);
	}

}


function uploadSuccess(file, server_data) {
	try {
		jQuery("#" + file.id + "progress").css("background", "url("+swfuCallbackL10n.progressBarUrl+") no-repeat -0px");
		jQuery("#" + file.id).attr("class", "SWFUploadFileItem uploadCompleted");
		jQuery("#" + file.id + "> a").before("<span class='okbtn'><!--IE--></span>");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadComplete(file) {
	try {
		/*  I want the next upload to continue automatically so I'll call startUpload here */
		if (this.getStats().files_queued > 0) {
			this.startUpload();
		} else {
			jQuery("#queueinfo").text(swfuCallbackL10n.allUp);
			jQuery("#commonInfo").slideDown('slow');
		}
	} catch (ex) {
		this.debug(ex);
	}
}


function cancelUpload() {
	try{
		var queuelength = swfu.getStats().files_queued;
		if(queuelength){	
			if(confirm(swfuCallbackL10n.cancelConfirm)){
				swfu.stopUpload();
				for(var index=0; index<queuelength; index++) {
					swfu.cancelUpload();
				}
			}
		}else
			window.location = window.location.href;
	} catch (ex) {
		this.debug(ex);
	}
}

