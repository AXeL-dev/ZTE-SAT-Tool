/* dragandrop js script */

// variables
var rowCount = 0;

// functions
function handleFileUpload(files, obj)
{
   for (var i = 0; i < files.length; i++)
   {
		if (!isExcelFile(files[i].name))
			alert('Please choose an Excel file !');
		else if (getCookieValue('files').indexOf(files[i].name) != -1) // if the same file exists
			alert(files[i].name + ' already exists !');
		else {
			// add to progress bar
			var status = new createStatusbar(obj);
			status.setFileNameSize(files[i].name, files[i].size);
			
			// send file to server
			var fd = new FormData();
			fd.append('uploadFile', files[i]);
			sendFileToServer(fd, status);
		}
   }
}

function sendFileToServer(formData, status)
{
    var uploadURL = "upload.php"; //Upload URL
    var extraData ={}; //Extra Data.
    var jqXHR=$.ajax({
            xhr: function() {
            var xhrobj = $.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        //Set progress
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
    url: uploadURL,
    type: "POST",
    contentType:false,
    processData: false,
        cache: false,
        data: formData,
        success: function(data){
            status.setProgress(100);
            //$("#status1").append("File upload Done<br>");
        }
    });
 
    status.setAbort(jqXHR);
}

function getCookieValue(NameOfCookie) {
	if (document.cookie.length > 0) {
		begin = document.cookie.indexOf(NameOfCookie+"=");
		if (begin != -1) {
			begin += NameOfCookie.length+1;
			end = document.cookie.indexOf(";", begin);
			if (end == -1) end = document.cookie.length;
			return unescape(document.cookie.substring(begin, end));
		}
	}
	
	return '';
}

function createCookie(name, value) {
	// create/update a temporary cookie (will be deleted after close browser)
	document.cookie = name+"="+value+";";
	// check for changes
	//alert(value);
	if (getCookieValue(name) == value)
		return true;
	else
		return false;
}

function addToCookie(cookiename, filename, filesize) {
	var value = getCookieValue(cookiename); // get old value of cookie
	if (value != null)
		value += filename + ':' + filesize + '|';
	else
		value = filename + ':' + filesize + '|';
	createCookie(cookiename, value);
	//alert(getCookieValue(cookiename));
}

function removeFromCookie(cookiename, filename, filesize) {
	// get value of cookie and change it
	var value = getCookieValue(cookiename);
	value = value.replace(filename + ':' + filesize + '|', '');
	// erase old value and put the new one
	createCookie(cookiename, value);
	//alert(getCookieValue(cookiename));
}

function convertSize(size) {
	var sizeKB = size/1024;
	if(parseInt(sizeKB) > 1024)
	{
		var sizeMB = sizeKB/1024;
		return sizeMB.toFixed(2)+" MB";
	}
	else
		return sizeKB.toFixed(2)+" KB";
}

function createStatusbar(obj)
{
     rowCount++;
     var row = "odd";
     if(rowCount % 2 == 0) row = "even";
     this.statusbar = $("<div class='statusbar "+row+"'></div>");
     this.filename = $("<div class='filename'></div>").appendTo(this.statusbar);
     this.size = $("<div class='filesize'></div>").appendTo(this.statusbar);
	 this.progressBar = $("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
     this.abort = $("<div class='abort'>Abort</div>").appendTo(this.statusbar);
	 this.createScript = $("<a class='createScript'>Create Script</a>").appendTo(this.statusbar);
	 this.del = $("<div class='delete'>Delete</div>").appendTo(this.statusbar);
	 this.showFile = $("<span class='showFileLabel'><input class='showFile' type='checkbox' />Show File</span>").appendTo(this.statusbar);
     obj.after(this.statusbar);
 
	// this is a function called after creating a status bar
    this.setFileNameSize = function(name, size)
    {
		// set name and size of file
		var sizeStr = convertSize(size);
		
        this.filename.html(name);
        this.size.html(sizeStr);

		// event click button delete
		this.del.click(function () {
			// we remove the current statusbar
				//var currentstatusbar = this.parentNode;
				//currentstatusbar.parentNode.removeChild(currentstatusbar);
			// with jquery better
			$(this).parent().fadeOut(300, function () {
				$(this).remove();
				removeFromCookie('files', name, sizeStr);
			});
		});

		// when check Show File
		this.showFile.children().change(function () {
			var createScriptAnchor = $(this).parent().prev().prev(); // recherche iéarchique / using DOM tree

			if ($(this).is(':checked')) {
				createScriptAnchor.attr('href', 'createScript.php?file=' + name + '&show=true');
				alert('Showing files having size greater than 1MB may freez the browser!');
			}
			else
				createScriptAnchor.attr('href', 'createScript.php?file=' + name + '&show=false');
		});

		// event click on anchor create Script
		this.createScript.click(function () {
			// delete loading images if exists
			$('.loadicon').remove();
			// adding loading image
			//$(this).before($("<img id='loadicon' src='icon/load.gif' />"));
			$(this).before($('<span class="loadicon"></span>'));
			
			//return false; // to stop redirecting
		});

		// set url (contains file name) to create script
		this.createScript.attr('href', 'createScript.php?file=' + name + '&show=false');

		// set cookie to remember files after refresh
		addToCookie('files', name, sizeStr);
    }
	this.setProgress = function(progress)
    {      
        var progressBarWidth =progress*this.progressBar.width()/ 100; 
        this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {
            this.abort.hide();
			this.progressBar.fadeOut(500, function () {
				$(this).next().remove(); // removing abort button
				$(this).remove();
			});
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.statusbar;
        this.abort.click(function()
        {
            jqxhr.abort();
			sb.fadeOut(300, function () {
				$(this).remove();
				// remove the cookie
				var fname = $(this).children()[0].innerHTML,
					fsize = $(this).children()[1].innerHTML;
				removeFromCookie('files', fname, fsize);
			});
        });
    }
}

// events
$(document).ready(function() {
	// Handle drag and drop events with jQuery
	var obj = $("#dragandrop");
	obj.on('dragenter', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
		$(this).css('border', '2px solid #52A8CA');
	});
	obj.on('dragover', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
	});
	obj.on('drop', function (e)
	{
		// drop is done
		$(this).css('border', '2px dashed #A9B6BD');
		e.preventDefault();
		var files = e.originalEvent.dataTransfer.files;
     	
		// check & upload file/files
		handleFileUpload(files, $('#browse'));
	});

	// prevent ‘drop’ event on document
	$(document).on('dragenter', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
	});
	$(document).on('dragover', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
		obj.css('border', '2px dashed #FF0030');
	});
	$(document).on('drop', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
		obj.css('border', '2px dashed #A9B6BD');
	});
});

