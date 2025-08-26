<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);

    $field_arr2=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr2);

    $c_rstatus_arr=array();
    \vwmldbm\code\get_code_name_all($c_rstatus_arr,'code_c_rstatus');
    
    $perm['R'] ='Y'; // Read permission
	$isAdmin = (Auth::check() && Auth::user()->isAdmin()) ? true : false;
    $isEresource = ($book->files ? true : false);


 // if the normal user logged in, and has reading history use it otherwise use book's auto_toc
    $tocOrigin = null; // ToC(Table of Contents) from either book or reading history
    $bookTocMode = false;
    $historyTocMode = false;

    if(!Auth::check() || $isAdmin) { // guest mode or admin
        if(!empty($book->auto_toc)) {
            $tocOrigin = $book->auto_toc;
            $bookTocMode = true;
        } 
    } else if(Auth::check() && !$isAdmin && !empty($readingHistory->historyData)) { // normal user with reading history
        $tocOrigin = $readingHistory->historyData;
        $historyTocMode = true;
    }


// book share link: In multi-inst mode, inst_uname is needed
    $bookShareLink = config('app.url','/mindbridger').'/book/'.$book->id; // default

    if(config('app.multi_inst') && !empty(config('app.inst_uname'))) {
        $bookShareLink = config('app.url','/mindbridger').'/book/inst/'.config('app.inst_uname').'/{$book->id}';
    }  
?>

@extends('layouts.root')
@section('content')
<!-- FontAwesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.img-icon-pointer {
    cursor:pointer;
    width:40px; 
    height:auto;
}

/* Action Icons Styling */
.action-icon-wrapper {
    align-items: center;
    margin-left: 1rem;
    height: 32px;
}

.action-icon-wrapper:first-child {
    margin-left: 0;
}

.action-icon-wrapper i {
    transition: all 0.3s ease;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-icon-wrapper i:hover {
    transform: scale(1.1);
    opacity: 0.8;
}

/* Share Popup Styles */
.share-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}

.share-popup {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.share-popup-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.share-popup-header h5 {
    margin: 0;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.share-popup-body {
    padding: 2rem;
}

.share-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.share-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    background: white;
    color: #495057;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.share-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #007bff;
}

.share-facebook:hover {
    background: #1877f2;
    color: white;
    border-color: #1877f2;
}

.share-twitter:hover {
    background: #1da1f2;
    color: white;
    border-color: #1da1f2;
}

.share-linkedin:hover {
    background: #0077b5;
    color: white;
    border-color: #0077b5;
}

.share-email:hover {
    background: #6c757d;
    color: white;
    border-color: #6c757d;
}

.share-btn i {
    font-size: 1.2rem;
    width: 24px;
    text-align: center;
    transition: all 0.3s ease;
}

.share-btn:hover i {
    transform: scale(1.1);
}

.share-url-section {
    border-top: 1px solid #e9ecef;
    padding-top: 1.5rem;
}

.share-url-section label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.75rem;
}

.share-url-section .input-group {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.share-url-section .form-control {
    border: none;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    background: #f8f9fa;
}

.share-url-section .btn {
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.share-url-section .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .share-popup {
        width: 95%;
        margin: 1rem;
    }
    
    .share-popup-header {
        padding: 1rem;
    }
    
    .share-popup-body {
        padding: 1.5rem;
    }
    
    .share-options {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .share-btn {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
}

// full-screen modal: Show the Book pages
/* Remove Bootstrap’s scrollbar-compensation padding on the modal element */
.edge-to-edge {
  padding: 0 !important;
}

/* Make the dialog truly full screen */
.modal-fullscreen {
  width: 100vw;         /* full viewport width */
  max-width: 100vw;     /* override BS4 max-width */
  height: 100vh;        /* full viewport height */
  margin: 0;            /* remove BS4 default .5rem margin */
}

/* Make the content fill the viewport */
.modal-fullscreen .modal-content {
  height: 100vh;
  border: 0;
  border-radius: 0;
  display: flex;
  flex-direction: column;
}

/* Let the body scroll independently if content overflows */
.modal-fullscreen .modal-body {
  flex: 1 1 auto;
  overflow: auto;
}

/* (Optional) ensure full-height baseline */
html, body { height: 100%; }
// End full-screen modal: Show the Book pages

/* Reading Progress Styles */
#start-reading-btn, #finish-reading-btn, #reset-reading-btn {
    transition: all 0.3s ease;
}

#start-reading-btn:hover, #finish-reading-btn:hover, #reset-reading-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Toast Notification Styles */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    z-index: 10000;
    display: none;
    max-width: 300px;
    border-left: 4px solid #007bff;
}

.toast-success {
    border-left-color: #28a745;
}

.toast-warning {
    border-left-color: #ffc107;
}

.toast-info {
    border-left-color: #17a2b8;
}

.toast-error {
    border-left-color: #dc3545;
}

/* Reading Time Info Styles */
#reading-time-info {
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

/* Book Content Styles */
.book-content-wrapper {
    background: white;
    overflow: hidden;
}

.book-header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.book-body {
    background: white;
    padding: 2rem;
}



</style>

@php
// url for PDFviewer. Ensure the PDF URL is correctly formed
    $rfiles=explode(';',$book->rfiles);
@endphp

<script>
  // Base path to PDF.js viewer page
  var viewerBase = @json(config('app.url') . '/lib/pdf.js/web/');

  function buildViewerUrl(rf, rid, start, end) {
    // If you need to pass the actual PDF file, set ?file=<encoded-pdf-url>
    // Your app seems to use rf/rid to resolve the file server-side.
    var q = '?file='
          + '&rf=' + encodeURIComponent(rf)
          + '&rid=' + encodeURIComponent(rid)
          + '&start=' + encodeURIComponent(start)
          + '&end=' + encodeURIComponent(end);

    // Tell PDF.js which page to start on via hash
    var hash = '#page=' + encodeURIComponent(start);

    return viewerBase + q + hash;
  }

  function goToPage(button) {
    // Read attributes
    var idx = button.getAttribute('data-idx');
    var page  = button.getAttribute('data-page');   // single anchor page
    var start = button.getAttribute('data-start') || page;
    var end   = button.getAttribute('data-end')   || page;
    var rf    = button.getAttribute('data-rf');
    var rid   = button.getAttribute('data-rid');
    var start_time   = button.getAttribute('data-start_time');
    var end_time   = button.getAttribute('data-end_time');
    var status   = button.getAttribute('data-status');

console.log("Go to page:", "idx", idx, "Page:", page, "Start:", start, "End:", end, "RF:", rf, "RID:", rid, "Status:", status, "start_time", start_time, "end_time", end_time); // debugging

    // Keep values on the modal instance for the lifecycle
    $('#fullscreenModal').data({ idx:idx, page: page, start: start, end: end, rf: rf, rid: rid, status: status });

    $('#fullscreenModal')
      .off('shown.bs.modal.pdf hidden.bs.modal.pdf')
      .on('shown.bs.modal.pdf', function () {
        var data = $(this).data();
        var viewerUrl = buildViewerUrl(data.rf, data.rid, data.start, data.end);

        document.getElementById('pdfIframe').src = viewerUrl;
        document.getElementById('fullscreenModalLabel').textContent =
          (data.start === data.end)
            ? ('Page ' + data.start)
            : ('Pages ' + data.start + '–' + data.end);

        document.getElementById('fullscreenModalLabel').innerHTML +=
            " <img src='{{ config('app.url','/mindbridger') }}/image/ai-assistant.png' " +
            " class='zoom img-icon-pointer ml-2' alt='AI Assistant' " +
            " data-toggle='modal' data-target='#aiModal' " +
            " data-rid='{{ $book->rid }}' data-start='" + start +"' data-end='" + end + "'> ";
        
            
      @if($historyTocMode)
        document.getElementById('fullscreenModalLabel').innerHTML +=
            " <button id='startButton' type='button' class='btn btn-primary ml-2' onClick=section_status_change('in_progress')>{{ __('Start Reading') }}</button>";
        document.getElementById('fullscreenModalLabel').innerHTML +=
            " <button id='finishButton' type='button' class='btn btn-warning ml-2' style='margin-left:10px;' onClick=section_status_change('completed')>{{ __('Finish') }}</button>";
        
        document.getElementById('fullscreenModalLabel').innerHTML +=
            " <button id='resetButton' type='button' class='btn btn-danger ml-2' style='margin-left:10px;display:none;' onClick=section_status_change('none')>{{ __('Reset') }}</button>";

        if (status == 'in_progress') {
            let displayTime = start_time.slice(0, 16); 
            document.getElementById('fullscreenModalLabel').innerHTML += " &nbsp; <span style='margin-left:10x;' class='small'>" + displayTime + " ~ </span>";
        } else if (status == 'completed') {
            let sTime = start_time.slice(0, 16); 
            let eTime = end_time.slice(0, 16); 
            document.getElementById('fullscreenModalLabel').innerHTML += " &nbsp; <span style='margin-left:10x;' class='small'>" + sTime + " ~ " + eTime + "</span>";
        } 

        // Button display according to the status of the section    
        $(document).ready(function () {
            if(status == '' || status =='none') {
                $('#startButton').show();
                $('#finishButton').hide();
            } else if(status == 'in_progress') {
                $('#startButton').hide();
                $('#finishButton').show(); 
            } else if(status == 'completed') {
                $('#startButton').hide();
                $('#finishButton').hide(); 
                $('#resetButton').show(); 
            }
        });
      @endif
      })
      .on('hidden.bs.modal.pdf', function () {
        document.getElementById('pdfIframe').src = 'about:blank'; // free resources
      })
      .modal('show');
  }

// change the section's status of the reading history
  function section_status_change(st) {
    var data = $('#fullscreenModal').data();
    if(st == 'none') {
        if(!confirm("{{ __('Do you want to reset the section status?') }}")) {
            return false;
        }
    }
    $.ajax({
        url: "{{ route('reading_history.section_set_status', ['book' => $book->id]) }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            idx: data.idx,              // ToC index
            status: st,          // New status
        },
        success: function (response) {
            console.log("Status updated:", response);
            if(st == 'completed') window.location.href = window.location.pathname; // don't have to reload the page
            else {
                window.location.href = window.location.pathname + "?idx=" + data.idx;
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Error updating section status.");
        }
    });
  }

</script>

<script>
  // this is to reopne the full screen section reading window after changing the section reading status
    window.addEventListener("load", function() {
        // Parse URL params
        let params = new URLSearchParams(window.location.search);
        let idx = params.get("idx");

        if (idx) {
            // Find button with that idx
            let btn = document.querySelector(`[data-idx="${idx}"]`);
            if (btn) {
                goToPage(btn);  // or btn.click()
            }

            // Optionally clear the param (so reload again won't repeat)
            // history.replaceState(null, "", window.location.pathname);
        }
    });

</script>

<div class="container mt-0">
    <div class="row">
        <div class="col-12">
                    <div class="book-content-wrapper">
            <div class="book-header d-flex justify-content-start align-items-center">
            @if($isAdmin)
                <span class="action-icon-wrapper">
                    <i class="fas fa-cog zoom img-icon-pointer" onClick="window.location='{{config('app.url','/mindbridger')."/book/".$book->id}}/edit'" style="cursor:pointer; font-size: 24px; color: #6c757d;"></i>
                </span>
            @endif
                <span class="action-icon-wrapper">
                    <i class="fas fa-share-alt zoom img-icon-pointer" onClick="openSharePopup()" style="cursor:pointer; font-size: 24px; color: #6c757d;"></i>
                </span>
                <script>
                     function openSharePopup() {
                        const shareUrl = '<?=$bookShareLink?>';
                        const shareTitle = '<?=addslashes($book->title)?>';
                        const shareText = '<?=addslashes($book->author)?> - <?=addslashes($book->title)?>';
                        
                        // Create share popup
                        const popup = document.createElement('div');
                        popup.className = 'share-popup-overlay';
                        popup.innerHTML = `
                            <div class="share-popup">
                                <div class="share-popup-header">
                                    <h5 class="mb-0">{{__("Share this resource")}}</h5>
                                    <button type="button" class="close-btn" onclick="closeSharePopup()">&times;</button>
                                </div>
                                <div class="share-popup-body">
                                    <div class="share-options">
                                        <button class="share-btn share-facebook" onclick="shareToFacebook('${shareUrl}', '${shareTitle}')">
                                            <i class="fab fa-facebook-f"></i>
                                            Facebook
                                        </button>
                                        <button class="share-btn share-twitter" onclick="shareToTwitter('${shareUrl}', '${shareText}')">
                                            <i class="fab fa-twitter"></i>
                                            Twitter
                                        </button>
                                        <button class="share-btn share-linkedin" onclick="shareToLinkedIn('${shareUrl}', '${shareTitle}', '${shareText}')">
                                            <i class="fab fa-linkedin-in"></i>
                                            LinkedIn
                                        </button>
                                        <button class="share-btn share-email" onclick="shareToEmail('${shareUrl}', '${shareTitle}', '${shareText}')">
                                            <i class="fas fa-envelope"></i>
                                            Email
                                        </button>
                                    </div>
                                    <div class="share-url-section">
                                        <label for="share-url" class="form-label">{{__("Or copy the link")}}:</label>
                                        <div class="input-group">
                                            <input type="text" id="share-url" class="form-control" value="${shareUrl}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                                                    {{__("Copy")}}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(popup);
                        document.body.style.overflow = 'hidden';
                    }
                    
                    function closeSharePopup() {
                        const popup = document.querySelector('.share-popup-overlay');
                        if (popup) {
                            popup.remove();
                            document.body.style.overflow = 'auto';
                        }
                    }
                    
                    function shareToFacebook(url, title) {
                        const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                        window.open(shareUrl, '_blank', 'width=600,height=400');
                    }
                    
                    function shareToTwitter(url, text) {
                        const shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
                        window.open(shareUrl, '_blank', 'width=600,height=400');
                    }
                    
                    function shareToLinkedIn(url, title, text) {
                        const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                        window.open(shareUrl, '_blank', 'width=600,height=400');
                    }
                    
                    function shareToEmail(url, title, text) {
                        const subject = encodeURIComponent(title);
                        const body = encodeURIComponent(`${text}\n\n${url}`);
                        const mailtoUrl = `mailto:?subject=${subject}&body=${body}`;
                        window.location.href = mailtoUrl;
                    }
                    
                    function copyShareUrl() {
                        const urlInput = document.getElementById('share-url');
                        urlInput.select();
                        urlInput.setSelectionRange(0, 99999);
                        navigator.clipboard.writeText(urlInput.value)
                            .then(() => {
                                const copyBtn = urlInput.nextElementSibling.querySelector('button');
                                const originalText = copyBtn.textContent;
                                copyBtn.textContent = '{{__("Copied!")}}';
                                copyBtn.classList.add('btn-success');
                                setTimeout(() => {
                                    copyBtn.textContent = originalText;
                                    copyBtn.classList.remove('btn-success');
                                }, 2000);
                            })
                            .catch((error) => {
                                alert(`{{__("Copy failed!")}} ${error}`);
                            });
                    }
                </script>

            @if(Auth::check() && !$isAdmin)
                <span class="action-icon-wrapper">
                    <!-- Favorite Checkbox with Heart Icon -->
                    <label style="cursor:pointer;" class="mb-0">
                        <input type="checkbox" id="favorite-checkbox" style="display:none;" onchange="toggleFavorite(this)">
                        <i id="favorite-icon" class="fa-heart zoom img-icon-pointer far" style="cursor:pointer; font-size: 24px; color: #dc3545;"></i>
                    </label>
                </span>
                <span class="action-icon-wrapper">
                <label style="cursor:pointer;">
                        <input type="checkbox" id="eshelf-checkbox" style="display:none;" onchange="toggleEshelf(this)">
                    <i id="eshelf-icon" class="far fa-bookmark zoom img-icon-pointer" 
                        style="font-size: 24px; color:#28a745;"></i>
                </label>
                </span>
                <button id="start-reading-btn" class="btn btn-success btn-sm ml-2" style="display: none;" onclick="showReadingStartModal()">
                    <i class="fas fa-play mr-1"></i>{{ __('Start Reading') }}
                </button>
                <button id="finish-reading-btn" class="btn btn-warning btn-sm ml-2" style="display: none;" onclick="finishReading()">
                    <i class="fas fa-stop mr-1"></i>{{ __('Finish') }}
                </button>
                <button id="reset-reading-btn" class="btn btn-danger btn-sm ml-2" style="display: none;" onclick="showResetWarning()">
                    <i class="fas fa-undo mr-1"></i>{{ __('Reset') }}
                </button>
                
                <!-- Reading Time Information -->
                <div id="reading-time-info" class="mt-2" style="display: none;">
                    <small class="text-muted">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="start-time-text"></span>
                        <span id="end-time-text"></span>
                    </small>
                </div>

                    @if($isEresource)
                     <label>
                        @php
                            if(empty($readingHistory) || $readingHistory->startable())
                                $sButtonDisplay="inline";
                            else $sButtonDisplay="none";
                        @endphp       
                        
                        <button type="button" style="display:{{$sButtonDisplay}};" id="startReadingButton"
                            class="btn btn-sm btn-warning ml-2">
                            {{ __('Start Reading') }}
                        </button>

                        @php
                            if(!empty($readingHistory) && $readingHistory->finishable())
                                $fButtonDisplay="inline";
                            else $fButtonDisplay="none";
                        @endphp
                        <span class="badge badge-info" style="display:{{$fButtonDisplay}};">
                            {{ __('In Progress') }}
                        </span>
                        <button type="button" style="display:{{$fButtonDisplay}};" id="finishReadingButton"
                                class="btn btn-sm btn-danger ml-2">
                            {{ __('Finish Reading') }}
                        </button>

                     </label>
                     @if(!empty($readingHistory) && $readingHistory->status == 'completed')
                      <label>     
                        <span class="badge badge-success">
                            {{ __('Finished') }}
                        </span>
                      </label>
                      <label>
                        <button type="button" id="resetReadingButton"
                                class="btn btn-sm btn-danger ml-2">
                            {{ __('Reset') }}
                        </button>
                      </label>
                    @endif

                    <label>
                      <img src='{{ config('app.url','/mindbridger') }}/image/ai-assistant.png' 
                        class='zoom img-icon-pointer ml-2' alt='AI Assistant' style='margine-left:10px;'
                        data-toggle="modal"
                        data-target="#metaModal"
                        data-book-id="{{ $book->id }}" title="{{ __('Book Info (AI)') }}"> 
                    </label>

                     <script>
                        let auto_toc_exist = false;
                        @if(!empty($book->auto_toc))
                            auto_toc_exist = true;
                        @endif
                        $(document).ready(function () {
                            $('#startReadingButton').on('click', function () {
                                if(!auto_toc_exist) {
                                    alert("{{__('Table of Contents is not available for this book. Please generate one first.')}}");
                                    return false; // Prevent further action
                                }
                                if(confirm("{{__("Do you want to start reading this book?")}}")) {
                                    $.ajax({
                                        url: "{{ route('reading_history.set_status', ['book' => $book->id]) }}",
                                        type: "POST",
                                        data: {
                                            status: "in_progress",
                                            operation: "create_history",
                                            book_id: "{{ $book->id }}",
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (response) {
                                            if (response.success) {
                                                console.log("Success:", response);
                                                document.getElementById('startReadingButton').style.display = 'none';
                                                document.getElementById('finishReadingButton').style.display = 'inline';
                                                document.getElementById('eshelf-checkbox').checked=true;
                                                toggleEshelf(document.getElementById('eshelf-checkbox'));
                      
                                                window.location.href = window.location.pathname; // clear section window qeury 
                                            
                                                /* alert("Let's start reading!"); */
                                            } else {
                                                console.warn("Unexpected response:", response);
                                                document.getElementById('startReadingButton').style.display = 'inline';
                                                document.getElementById('finishReadingButton').style.display = 'none';
                                                alert("Failed to update status.");
                                            }
                                        },
                                        error: function (xhr) {
                                            console.error("Error:", xhr.responseText);
                                            document.getElementById('startReadingButton').style.display = 'inline';
                                            document.getElementById('finishReadingButton').style.display = 'none';
                                            alert("Something went wrong!");
                                        }
                                    });
                                } else {
                                    return false; // User cancelled
                                }
                            });

                            $('#finishReadingButton').on('click', function () {
                                if(confirm("{{__("Do you want to finish reading this book?")}}")) {
                                    $.ajax({
                                        url: "{{ route('reading_history.set_status', ['book' => $book->id]) }}",
                                        type: "POST",
                                        data: {
                                            status: "completed",
                                            book_id: "{{ $book->id }}",
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (response) {
                                            if (response.success) {
                                                console.log("Success:", response);
                                                document.getElementById('finishReadingButton').style.display = 'none';
                                                /* alert("Reading finished!"); */
                                                window.location.href = window.location.pathname; // clear section window qeury 
                                            } else {
                                                console.warn("Unexpected response:", response);
                                                document.getElementById('finishReadingButton').style.display = 'inline';
                                                alert("Failed to update status.");
                                            }
                                        },
                                        error: function (xhr) {
                                            console.error("Error:", xhr.responseText);
                                            document.getElementById('finishReadingButton').style.display = 'inline';
                                            alert("Something went wrong!");
                                        }
                                    });
                                }
                            });


                            $('#resetReadingButton').on('click', function () {
                                if(confirm("{{__("Do you want to reset reading history of the book? All data will be lost!")}}")) {
                                    $.ajax({
                                        url: "{{ route('reading_history.destroy', ['book' => $book->id]) }}",
                                        type: "POST",
                                        data: {
                                            book_id: "{{ $book->id }}",
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (response) {
                                            if (response.success) {
                                                console.log("Success:", response);
                                                document.getElementById('resetReadingButton').style.display = 'none';
                                                /* alert("Reading finished!"); */
                                                window.location.href = window.location.pathname; // clear section window qeury 
                                            } else {
                                                console.warn("Unexpected response:", response);
                                                document.getElementById('resetReadingButton').style.display = 'inline';
                                                alert("Failed to update status.");
                                            }
                                        },
                                        error: function (xhr) {
                                            console.error("Error:", xhr.responseText);
                                            document.getElementById('resetReadingButton').style.display = 'inline';
                                            alert("Something went wrong!");
                                        }
                                    });
                                }
                            });
                        });

                    </script>
                    @endif                    
                    <script>
                            let isFavorited = false;
                            let isEshelfOn = false;

                            $.get("{{ route('book.favorite.check', ['book' => $book->id]) }}")
                                .done(function (response) {
                                    if (response.favorited) {
                                        isFavorited = true;
                                    $('#favorite-icon').removeClass('far').addClass('fas'); // filled
                                        $('#favorite-checkbox').prop('checked', true);
                                    }
                                });

                            $(document).ready(function () {
                                if (isFavorited) {
                                    $('#favorite-checkbox').prop('checked', true);
                                $('#favorite-icon').removeClass('far').addClass('fas'); // filled
                            } else {
                                $('#favorite-checkbox').prop('checked', false);
                                $('#favorite-icon').removeClass('fas').addClass('far'); // outline
                                }
                            });

                            // For My E-Shelf
                            $.get("{{ route('book.eshelf.check', ['book' => $book->id]) }}")
                                .done(function (response) {
                                    if (response.isMyEshelf) {
                                        $('#eshelf-checkbox').prop('checked', true);
                                $('#eshelf-icon').removeClass('far').addClass('fas');
                            } else {
                                $('#eshelf-checkbox').prop('checked', false);
                                $('#eshelf-icon').removeClass('fas').addClass('far');
                                    }
                                });

                            $(document).ready(function () {
                                if (isEshelfOn) {
                                    $('#eshelf-checkbox').prop('checked', true);
                                $('#eshelf-icon').removeClass('far').addClass('fas');
                            } else {
                                $('#eshelf-checkbox').prop('checked', false);
                                $('#eshelf-icon').removeClass('fas').addClass('far');
                                }
                            });

                            function toggleFavorite(checkbox) {
                                let isChecked = checkbox.checked;
                                let icon = $('#favorite-icon');

                            if (isChecked) { // add favorite
                                icon.removeClass('far').addClass('fas'); // solid heart
                                    $.post("{{ route('book.favorite.store', ['book' => $book->id]) }}", {
                                        _token: '{{ csrf_token() }}'
                                    });

                                $('#favModal').modal('show');
                                setTimeout(() => {
                                    $('#favModal').modal('hide');
                                }, 1000); // auto-hide after 2s

                            } else { // remove favorite
                                icon.removeClass('fas').addClass('far'); // outline heart
                                    $.ajax({
                                        url: "{{ route('book.favorite.remove', ['book' => $book->id]) }}",
                                        type: 'DELETE',
                                        data: { _token: '{{ csrf_token() }}' }
                                    });

                                $('#favModalD').modal('show');
                                setTimeout(() => {
                                    $('#favModalD').modal('hide');
                                }, 1000); // auto-hide after 2s
                                }
                            }

                            function toggleEshelf(checkbox) {
                                let icon = $('#eshelf-icon');
                                
                                if (checkbox.checked) { // add to e-shelf
                                    icon.removeClass('far fa-bookmark').addClass('fas fa-bookmark'); // solid bookmark
                                    $.post("{{ route('book.eshelf.store', ['book' => $book->id]) }}", {
                                        _token: '{{ csrf_token() }}'
                                    });
                                    $('#eshelfModal').modal('show');
                                    setTimeout(() => {
                                        $('#eshelfModal').modal('hide');
                                    }, 1000);
                                } else { // remove from e-shelf
                                    icon.removeClass('fas fa-bookmark').addClass('far fa-bookmark'); // outline bookmark
                                    $.ajax({
                                        url: "{{ route('book.eshelf.remove', ['book' => $book->id]) }}",
                                        type: 'DELETE',
                                        data: { _token: '{{ csrf_token() }}' }
                                    });
                                    $('#eshelfModalD').modal('show');
                                    setTimeout(() => {
                                        $('#eshelfModalD').modal('hide');
                                    }, 1000);
                                }
                            }
                    </script>
                </span>
            @endif
            </div>
        
        <!-- Favorite Small Modal -->
            <div class="modal fade" id="favModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content text-center">
                    <div class="modal-body">
                        <p id='fav_small_p' class="mb-0 text-success">{{ __('Added to your favorite list') }}</p>
                    </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="favModalD" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content text-center">
                    <div class="modal-body">
                        <p id='fav_small_p' class="mb-0 text-info">{{ __('Removed from your favorite list') }}</p>
                    </div>
                    </div>
                </div>
            </div>
        
            <!-- Eshelf Small Modal -->
            <div class="modal fade" id="eshelfModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content text-center">
                    <div class="modal-body">
                        <p id='fav_small_p' class="mb-0 text-success">{{ __('Added to your e-Shelf list') }}</p>
                    </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="eshelfModalD" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content text-center">
                        <div class="modal-body">
                            <p id='fav_small_p' class="mb-0 text-info">{{ __('Removed from your e-Shelf list') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
        <!-- Reset Warning Modal -->
        <div class="modal fade" id="resetWarningModal" tabindex="-1" role="dialog" aria-labelledby="resetWarningModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetWarningModalLabel">
                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>경고
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="mb-0">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        </p>
                        <h5 class="mt-3 text-warning">{{__("All reading history will be deleted")}}</h5>
                        <p class="text-muted">모든 독서 기록이 삭제됩니다. 계속하시겠습니까?</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
                        <button type="button" class="btn btn-danger" onclick="resetReading()">
                            <i class="fas fa-trash mr-1"></i>삭제
                        </button>
                    </div>
                </div>
            </div>
        </div>
            </div>
            <div class="book-body">
            <div class="card-body col-12">
                <form method="POST" name='form1' id='pform' action="{{config('app.url','/mindbridger')."/book/".$book->id}}" enctype="multipart/form-data">
                    @csrf 
                    <input type='hidden' name='_method' value='PUT'>
                    <input type='hidden' name="progress_up_flag">                   
                    <input type='hidden' name="id" value='{{$book->id}}'>
                    <input type='hidden' name="del_file">                   
                    <div class="form-group row">
                        <label for="title" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Title") }}</label>

                        <div class="col-md-9">
                            <div class='container border-0 mt-0'>{{ $book->title }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="author" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Author") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->author }}</div>
                        </div>
                    </div>

                    @if($isEresource) 
                        @auth
                            <?PHP
                                $video_txt=show_list_videos($book,$perm,$book->rid);
                            ?>
                            @if($video_txt!='')
                                <div class="form-group row">
                                    <label for="video" class="col-md-3 col-form-label text-md-right font-weight-bold">{{__('Video') }}</label>
                                    <div class="col-md-9">                                     
                                        <?=$video_txt?>                                                         
                                    </div>
                                </div>
                            @endif
                        @endauth
                        
                        <div class="form-group row">
                            <label for="e-Resources" class="col-md-3 col-form-label text-md-right font-weight-bold">
                                {{ __('e-Resources') }}                               
                            </label>
                            @if(Auth::check() || $book->e_res_af_login_yn!='Y') 
                            <div class="col-md-9">
                                <?PHP 
                                    echo show_list_old_files($book,$perm,$book->rid);                                
                                ?>
                                <ol id='fileList'></ol>                               
                                                            
                            </div>
                            @elseif($book->e_resource_yn=='Y' && $book->e_res_af_login_yn=='Y')
                                <div class="col-md-9">
                                <span style='color:magenta;'>
                                    {{__("Log in required")}}
                                </span>
                                </div>
                            @endif
                        </div>

                    @endif

                <!-- ToC Display: Accordion -->

                @if(!empty($tocOrigin))
                    <div class="form-group row">
                        <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">
                            {{ __("Table of Contents") }}
                        </label>

                        <div class="col-md-9">
                            <div class="accordion" id="tocAccordion">
                            @php
                        // Get ToC from old input or model
                            $tocJson = $tocOrigin ?? [];

                        $toc = is_array($tocJson) ? $tocJson : json_decode($tocJson, true) ?? [];

                        // Group: level 1 = chapters; attach following items with level > 1 as children until next level 1
                        $chapters = [];
                        $current = null;

                        foreach ($toc as $item) {
                                
                            $item = [
                                    'idx' => $item['idx'] ?? 0,
                                'title' => $item['title'] ?? 'Untitled',
                                    'page'  => $item['page'] ?? ($item['start'] ?? null),   // legacy single page
                                'start' => $item['start'] ?? ($item['page'] ?? null),
                                'end'   => $item['end']   ?? ($item['page'] ?? null),
                                'level' => $item['level'] ?? 1,
                                    'status' => $item['status'] ?? '',
                                    'start_time' => $item['start_time'] ?? '',
                                    'end_time' => $item['end_time'] ?? '',
                            ];

                            if ($item['level'] <= 1) {
                                if ($current) $chapters[] = $current;

                                $current = [
                                        'idx' =>    $item['idx'],
                                    'title'    => $item['title'],
                                        'page'     => $item['page'] ?? ($item['start'] ?? null),
                                    'start'    => $item['start'],
                                    'end'      => $item['end'],
                                        'status' => $item['status'] ?? '',
                                        'start_time' => $item['start_time'] ?? '',
                                        'end_time' => $item['end_time'] ?? '',
                                    'children' => [],
                                ];
                            } else {
                                if (!$current) { // in case JSON starts with > level 1
                                    $current = [
                                            'idx' => $item['idx'],
                                        'title'    => 'Chapter',
                                        'page'     => null,
                                        'start'    => null,
                                        'end'      => null,
                                            'status' => $item['status'] ?? '',
                                            'start_time' => $item['start_time'] ?? '',
                                            'end_time' => $item['end_time'] ?? '',
                                        'children' => [],
                                    ];
                                }
                                $current['children'][] = $item;
                            }
                        }
                        if ($current) $chapters[] = $current;
                        @endphp

                            </div>
                            
                        <!-- Fullscreen Modal: For the ranges of book -->
                        <div class="modal fade edge-to-edge" id="fullscreenModal" tabindex="-1" role="dialog"
                            aria-labelledby="fullscreenModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-fullscreen" role="document">
                                <div class="modal-content">
                                    <div class="modal-header py-2">
                                        <h5 class="modal-title" id="fullscreenModalLabel">Document Viewer</h5>
                                        <button type="button " class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true"'>&times; </span>
                                        </button>
                                    </div>

                                    <div class="modal-body p-0">
                                        <!-- PDF.js iframe -->
                                        <iframe id="pdfIframe" title="PDF Viewer"
                                                style="width:100%; height:100%; border:0;" allow="fullscreen"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Fullscreen Modal: For the ranges of book -->

                        <div id="tocAccordion">
                    @foreach($chapters as $idx => $ch)
                        @php
                            $collapseId = "tocCollapse{$idx}";
                            $headingId  = "tocHeading{$idx}";
                            $startPage  = $ch['start'] ?? $ch['page'];
                            $endPage    = $ch['end'] ?? $ch['page'];
                                $status    = $ch['status'] ?? '';
                                $start_time  = $ch['start_time'] ?? '';
                                $end_time    = $ch['end_time'] ?? '';
                                $hasChildren = !empty($ch['children']);
                        @endphp

                        <div class="card">
                        <div class="card-header" id="{{ $headingId }}">
                            <h5 class="mb-0 d-flex align-items-center justify-content-between">                      
                                        {{-- If chapter has children, make title clickable to expand --}}
                                        @if($hasChildren)
                                            <button class="btn btn-link" type="button"  
                                                    data-idx="{{ $ch['idx'] }}"
                                                    data-toggle="collapse"
                                                    data-target="#{{ $collapseId }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="{{ $collapseId }}"
                                data-start="{{ $startPage }}"
                                data-end="{{ $endPage }}"
                                data-rf="{{ e($rfiles[0]) }}"
                                data-rid="{{ e($book->rid) }}"
                                                    data-status="{{ $status }}"
                                                    data-start_time="{{ $start_time }}"
                                                    data-end_time="{{ $end_time }}">

                                {{ $ch['title'] }}
                                <span class="badge badge-secondary ml-2">
                                    @if($startPage !== $endPage)
                                    pp. {{ $startPage }}–{{ $endPage }}
                                    @else
                                    p. {{ $startPage }}
                                    @endif
                                </span>
                            </button>
                                        @else
                                            {{-- No children: plain text title, no toggle --}}
                                            <?php
                                            if($ch['status'] == 'in_progress') {
                                                $badge = "<span class='badge badge-warning'>".__("Reading..")."</span>";
                                            } else if($ch['status'] == 'completed') {
                                                $badge = "<span class='badge badge-success'>".__("Done :)")."</span>";
                                            } else $badge ="";
                                            ?>
                                            <span class="btn" style="pointer-events: none; cursor: default;">
                                                {!! $badge !!} {{ $ch['title'] }}
                                                <span class="badge badge-secondary ml-2">
                                                    @if($startPage !== $endPage)
                                                        pp. {{ $startPage }}–{{ $endPage }}
                                                    @else
                                                        p. {{ $startPage }}
                                                    @endif
                                                </span>
                                            </span>
                                        @endif

                                        {{-- Go to page button always available --}}
                            @if(!is_null($ch['page']))
                                <button type="button"
                                        class="btn btn-sm btn-primary ml-2"
                                                    data-idx="{{ $ch['idx'] }}"
                                        data-start="{{ $startPage }}"
                                        data-end="{{ $endPage }}"
                                        data-rf="{{ e($rfiles[0]) }}"
                                        data-rid="{{ e($book->rid) }}"
                                                    data-status="{{ $status }}"
                                                    data-start_time="{{ $start_time }}"
                                                    data-end_time="{{ $end_time }}"
                                        onclick="goToPage(this)">
                                {{ __('Go to page') }}
                                </button>
                            @endif
                            </h5>
                        </div>

                                {{-- Only render collapsible body if children exist --}}
                                @if($hasChildren)
                        <div id="{{ $collapseId }}" class="collapse" aria-labelledby="{{ $headingId }}" data-parent="#tocAccordion">
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                @foreach($ch['children'] as $child)
                                    @php
                                                        $cStart = $child['start'] ?? $child['page'];
                                                        $cEnd   = $child['end'] ?? $child['page'];

                                                        if($child['status'] == 'in_progress') {
                                                            $badge = "<span class='badge badge-warning'>".__("Reading..")."</span>";
                                                        } else if($child['status'] == 'completed') {
                                                            $badge = "<span class='badge badge-success'>".__("Done :)")."</span>";
                                                        }  else $badge =""; 

                                    @endphp
                                    <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <span>
                                        @if(($child['level'] ?? 2) > 2)
                                        <span class="text-muted mr-2" style="display:inline-block; width: {{ (($child['level']-2)*14) }}px;"></span>
                                        @endif
                                                            {!! $badge !!} {{ $child['title'] }}
                                    </span>

                                    @if(!is_null($child['page']))
                                        <button type="button"
                                                                    class="btn btn-outline-secondary btn-sm"
                                                                    data-idx="{{ $child['idx'] }}"
                                                data-start="{{ $cStart }}"
                                                data-end="{{ $cEnd }}"
                                                data-rf="{{ e($rfiles[0]) }}"
                                                data-rid="{{ e($book->rid) }}"
                                                                    data-status="{{ $child['status'] }}"
                                                                    data-start_time="{{ $child['start_time'] }}"
                                                                    data-end_time="{{ $child['end_time'] }}"
                                                onclick="goToPage(this)">
                                        @if($cStart !== $cEnd)
                                            pp. {{ $cStart }}–{{ $cEnd }}
                                        @else
                                            p. {{ $cStart }}
                                        @endif
                                        </button>
                                    @endif
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                        </div>
                                @endif
                        </div>
                    @endforeach
                    </div>
                        </div>
                <script>
                    $(document).ready(function () {
                        @if($historyTocMode)
                            // Expand all accordion panels
                            $('#tocAccordion .collapse').each(function () {
                                $(this).collapse('show');
                            });
                        @endif
                    });
                </script>
                    </div>
                    @else
                        <div class="form-group row">
                            <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Table of Contents") }}</label>
                            <div class="col-md-9">
                                {{ __("No Table of Contents available.") }}
                            </div>
                        </div>
                    @endif
                <!-- End ToC Display: Accordion -->

                    <div class="form-group row">
                        <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Type") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_rtype',$book->c_rtype)?></div>
                        </div>
                    </div>

                @if(\vwmldbm\code\is_code_usable('code_c_genre'))    
                    <div class="form-group row">
                        <label for="c_genre" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Genre") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_genre',$book->c_genre)?></div>
                        </div>
                    </div>
                @endif

                @if(\vwmldbm\code\is_code_usable('code_c_grade'))
                    <div class="form-group row">
                        <label for="c_grade" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Grade") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_grade',$book->c_grade)?></div>
                        </div>
                    </div>
                @endif

                @if(\vwmldbm\code\is_code_usable('code_c_category'))
                    <div class="form-group row">
                        <label for="c_category" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Category") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_category',$book->c_category)?></div>
                        </div>
                    </div>
                @endif
                
                @if(\vwmldbm\code\is_code_usable('code_c_category2'))
                    <div class="form-group row">
                        <label for="c_category2" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Category2") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_category2',$book->c_category2)?></div>
                        </div>
                    </div>
                @endif

                    <div class="form-group row">
                        <label for="c_lang" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Language") }}</label>

                        <div class="col-md-9">
                            <span class='form-control border-0'>
                                <?=\vwmldbm\code\print_code('vwmldbm_c_lang',$book->c_lang,'c_lang',null,null,null,'RD_ONLY',null,"class='form-control'");?>
                            </span>
                        </div>
                    </div>
                    

                    <div class="form-group row">
                        <label for="e_resource_yn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("e-Resource") }}</label>
                        <div class="col-md-9">
                            <div class='form-control border-0'>                            
                                <?PHP
                                echo \vwmldbm\code\print_c_yn('e_resource_yn',$book->e_resource_yn,null,'RD_ONLY',null,'Y_BLUE');
                                ?>
                            </div>                             
                        </div>
                    </div>                                    

                    <div class="form-group row">
                        <label for="desc" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Description") }}</label>

                        <div class="col-md-9">
                            <div class='form-control-static overflow-auto' style='max-height:600px;min-height:100px;'>
                                <?=stripslashes($book->desc)?>
                            </div>                         
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="publisher" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Publisher") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->publisher }}</div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="publisher" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("URL") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><a href='{{ $book->url }}' target='_blank'>{{ $book->url }}</a></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="pub_date" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Pub Date") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->pub_date }}</div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="isbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("ISBN") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->isbn }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="eisbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("eISBN") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->eisbn }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="keywords" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Keywords") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->keywords }}</div>                               
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="price" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Price") }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->price }}</div>                               
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="cover_image" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Cover Image") }}</label>
                        <div class="col-md-9">
                            @if($book->cover_image)
                                <img onClick='open_cover_img(this)' style='cursor:pointer;' src='{{config('app.url','/nwlibrary')}}/storage/cover_images/{{$_SESSION['lib_inst']}}/{{$book->cover_image}}' height='200'>
                                <script>  
                                    $(document).ready(function (){
                                        $( "#dialog" ).dialog({
                                            width:'auto',
                                            height:'auto',
                                            maxWidth:'400',
                                            autoOpen: false,
                                            position: {
                                                my: 'middle',
                                                at: 'top',
                                                of: this,
                                            }
                                        });
                                    });

                                    function open_cover_img(obj) {
                                        $('#dialog').dialog('open');    
                                        $('#dialog_img').attr("src",obj.src);                                            
                                    }
                                </script>

                                <div id="dialog" title="" style="display:none; align-top;">
                                    <img id='dialog_img' width='100%'>
                                </div>    
                            @endif
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <div class="col-md-9 offset-md-4">                               
                            <button type="button" class="btn btn-success" onClick="window.history.back();">
                                {{ __('Go Back') }}
                            </button>
                        </div>                          
                    </div>                                           
                </form>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
        <table class="table table-striped table-responsive-md">
        <tr>
            <th> </th>
                    <th>{{__("barcode")}}</th>
                    <th>{{__("call_no")}}</th>
                    <th>{{__("location")}}</th>
                    <th>{{__("c_rstatus")}}</th>
        </tr>
        <?PHP $cnt=1; ?>
        @foreach($book_copy as $bc)                  
            <tr>
                <td>{{$cnt++}}</td> 
                <td>{{$bc['barcode']}}</td>
                <td>{{$bc['call_no']}}</td>
                <td>{{$bc['location']}}</td>
                <td>
                    <?PHP
                        if(isset($c_rstatus_arr[$bc['c_rstatus']])) echo $c_rstatus_arr[$bc['c_rstatus']];
                    ?>
                </td>
            </tr>
        @endforeach
        </table>
        </div>
    </div>
</div>
<br><br>

<!-- AI Assistant Modal -->
<div class="modal fade" id="aiModal" tabindex="-1" role="dialog" aria-labelledby="aiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aiModalLabel">{{__("AI Assistant Notes")}}</h5>
        <button type="button" 
                class="btn btn-warning rounded-circle" 
                style="font-size: 1rem; padding: 0.25rem 0.5rem;" 
                data-dismiss="modal" 
                aria-label="Close">
        {{ __("Close") }}
        </button>
      </div>

      <div class="modal-body" id="aiModalBody">
        <p class="text-muted">Loading description...</p>
      </div>

      <!-- ✅ Centered Refresh Button -->
      <div class="modal-footer d-flex justify-content-center">
        <button id="refreshAiBtn" class="btn btn-sm btn-outline-primary">
          ↻ {{_("Refresh") }}
        </button>
        <button id="saveAiBtn" class="btn btn-sm btn-success" style="margin-left:10px;" disabled>
            💾 {{__("Save")}}
        </button>

        <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal" style="margin-left:10px;">
            {{__("Close")}}
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// AI Assistant
$('#aiModal').on('show.bs.modal', function (event) {
    let trigger = $(event.relatedTarget);
    let $body   = $('#aiModalBody');

    // Get start & end from trigger
    let start   = trigger.data('start');
    let end     = trigger.data('end');

    // Store them in modal for reuse
    $(this).data('start', start);
    $(this).data('end', end);

    // ✅ If already loaded, skip reload
    if ($body.data('loaded')) return;

    $body.html("<p class='text-muted'>Fetching {{__("AI explanation")}}...</p>");

    fetchAiData(start, end, $body);
    console.log("A:",start,end);
});

function fetchAiData(start, end, $body) {
    $.ajax({
        url: "{{ route('reading_history.section_ai', $book->id) }}",
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            start_page: start,
            end_page: end
        },
        success: function(response) {
            if (response.meta_data && response.meta_data.explanation) {
                let explanation = `
                    <div class='p-3'>
                        <h5 class='mb-3'>
                            <i class='fas fa-robot text-primary mr-2'></i>{{__("AI explanation")}}
                        </h5>
                        <div class='border rounded p-3 bg-light mb-4'>
                            <p style='white-space: pre-wrap;'>${response.meta_data.explanation}</p>
                        </div>
                    </div>
                `;

                let questionsHtml = "<h5>{{__("True/False Questions")}}</h5><ol>";
                response.meta_data.questions.forEach((q, i) => {
                    questionsHtml += `
                        <li class="mb-3">
                            <span>${q.q}</span><br>
                            <button class="btn btn-sm btn-outline-success mr-2" onclick="checkAnswer(${i}, true)">{{__("True")}}</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="checkAnswer(${i}, false)">{{__("False")}}</button>
                            <div id="answerBox-${i}" class="mt-2" style="display:none;"></div>
                        </li>
                    `;
                });
                questionsHtml += "</ol>";

                $body.html(explanation + questionsHtml);

                // Save correct answers
                window.correctAnswers = response.meta_data.questions.map(q => q.answer);

                // Enable Save button
                $('#saveAiBtn').prop('disabled', false);

                // Mark as loaded
                $body.data('loaded', true);
            } else {
                $body.html("<p class='text-danger'>No explanation found.</p>");
                $('#saveAiBtn').prop('disabled', true);
            }
        },
        error: function(xhr) {
            if (xhr.status === 404) {
                $body.html("<p class='text-danger'>PDF Text is not available :(</p>");
            } else {
                $body.html("<p class='text-danger'>Failed to load AI description.</p>");
            }
            $('#saveAiBtn').prop('disabled', true);
        }
    });
}

$('#refreshAiBtn').on('click', function() {
    let $modal = $('#aiModal');
    let $body  = $('#aiModalBody');

    // Reset loaded flag
    $body.removeData('loaded');

    // Get saved start/end from modal
    let start = $modal.data('start');
    let end   = $modal.data('end');
    console.log("B:",start,end);
    // Reload content
    $body.html("<p class='text-muted'>Refreshing {{__("AI explanation")}}...</p>");
    fetchAiData(start, end, $body);
});


function checkAnswer(index, choice) {
    let correct = window.correctAnswers[index];
    let box = document.getElementById(`answerBox-${index}`);

    if (choice === correct) {
        box.className = "alert alert-success mt-2 p-2";
        box.innerText = "✅ Correct!";
    } else {
        box.className = "alert alert-danger mt-2 p-2";
        box.innerText = "❌ Incorrect. Try again.";
    }
    box.style.display = "block";
}

</script>



<!-- Book Meta Modal -->
<div class="modal fade" id="metaModal" tabindex="-1" role="dialog" aria-labelledby="metaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="metaModalLabel">{{__("Book Information (AI)")}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body" id="metaModalBody">
        <p class="text-muted">Loading meta info...</p>
      </div>
    </div>
  </div>
</div>

<script>
$('#metaModal').on('show.bs.modal', function (event) {
    let trigger = $(event.relatedTarget);
    let bookId  = trigger.data('book-id');
    let $body   = $('#metaModalBody');

    $body.html("<p class='text-muted'>Fetching book meta...</p>");

    $.ajax({
        url: "{{ route('get_meta', ':id') }}".replace(':id', bookId),
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.message) {
                // Show big centered message
                $('#metaModalBody').html(`
                    <div class="alert alert-warning text-center">
                        ${response.message}
                    </div>
                `);
                return;
            }
            console.log(response);
            let metaHtml = `
            <ul class="list-group text-left">
                <li class="list-group-item"><strong>Title:</strong> ${response.title || '-'}</li>
                <li class="list-group-item"><strong>Author:</strong> ${response.author || '-'}</li>
                <li class="list-group-item"><strong>Genre:</strong> ${response.genre || '-'}</li>
                <li class="list-group-item"><strong>Category:</strong> ${response.category || '-'}</li>
                <li class="list-group-item"><strong>Difficulty:</strong> ${response.difficulty || '-'}</li>
                <li class="list-group-item"><strong>Theme:</strong> ${response.theme || '-'}</li>
            </ul>
            <div class="mt-3">
                <h6>Summary</h6>
                <p style="white-space: pre-wrap;">${response.summary || '-'}</p>
            </div>
            `;
            $('#metaModalBody').html(metaHtml);
        },
        error: function(xhr) {
            if (xhr.status === 404) {
                $('#metaModalBody').html("<p class='text-danger'>{{__("No meta data found for this book.")}}</p>");
            } else {
                $('#metaModalBody').html("<p class='text-danger'>{{__("Failed to load meta data.")}}</p>");
            }
        }
    });
});
</script>
@endsection