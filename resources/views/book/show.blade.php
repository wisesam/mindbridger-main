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
    display: inline-flex;
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
    var page  = button.getAttribute('data-page');   // single anchor page
    var start = button.getAttribute('data-start') || page;
    var end   = button.getAttribute('data-end')   || page;
    var rf    = button.getAttribute('data-rf');
    var rid   = button.getAttribute('data-rid');

    // Keep values on the modal instance for the lifecycle
    $('#fullscreenModal').data({ page: page, start: start, end: end, rf: rf, rid: rid });

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
            " <img src='{{ config('app.url','/wlibrary') }}/image/ai-assistant.png' class='zoom img-icon-pointer ml-2' alt='AI Assistant'>";

      @if($historyTocMode)
        document.getElementById('fullscreenModalLabel').innerHTML +=
            " <button id='startButton' class='btn btn-primary ml-2'>{{ __('Start Reading') }}</button>";
        document.getElementById('fullscreenModalLabel').innerHTML +=
            " <button id='finishButton' class='btn btn-warning' style='margin-left:10px;display:none;'>{{ __('Finish') }}</button>";
      

       <?php
        
       ?>
      @endif

      })
      .on('hidden.bs.modal.pdf', function () {
        document.getElementById('pdfIframe').src = 'about:blank'; // free resources
      })
      .modal('show');
  }
</script>

<div class="row justify-content-center">
    <div class="container col-12 mt-0">
        <div class="card">
            <div class="card-header col-12">
            @if($isAdmin)
                <span class="action-icon-wrapper">
                    <i class="fas fa-cog zoom img-icon-pointer" onClick="window.location='{{config('app.url','/wlibrary')."/book/".$book->id}}/edit'" style="cursor:pointer; font-size: 24px; color: #6c757d;"></i>
                </span>
            @endif
                <span class="action-icon-wrapper">
                    <i class="fas fa-share-alt zoom img-icon-pointer" onClick="openSharePopup()" style="cursor:pointer; font-size: 24px; color: #6c757d;"></i>
                </span>
                <script>
                     function openSharePopup() {
                        const shareUrl = '<?=config('app.url','/wlibrary')."/inst/".session('inst_uname')."/book/".$book->id?>';
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
                        <i id="favorite-icon" class="far fa-heart zoom img-icon-pointer" style="cursor:pointer; font-size: 24px; color: #dc3545;"></i>
                    </label>
                </span>
                <span class="action-icon-wrapper">
                    <label style="cursor:pointer;">
                        <input type="checkbox" id="eshelf-checkbox" style="display:none;" onchange="toggleEshelf(this)">
                        <i id="eshelf-icon" class="fas fa-book-open zoom img-icon-pointer" style="cursor:pointer; font-size: 24px; color: #28a745;"></i>
                    </label>
                </span>
                <button id="start-reading-btn" class="btn btn-success btn-sm ml-2" style="display: none;" onclick="showReadingStartModal()">
                    <i class="fas fa-play mr-1"></i>Start Reading
                </button>
                <button id="finish-reading-btn" class="btn btn-warning btn-sm ml-2" style="display: none;" onclick="finishReading()">
                    <i class="fas fa-stop mr-1"></i>Finish Reading
                </button>
                <button id="reset-reading-btn" class="btn btn-danger btn-sm ml-2" style="display: none;" onclick="showResetWarning()">
                    <i class="fas fa-undo mr-1"></i>Reset
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
                                                location.reload();
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
                                                location.reload();
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
                                                location.reload();
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
                                    $('#favorite-icon').attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-filled2.png');
                                    $('#favorite-checkbox').prop('checked', true);
                                }
                            });

                        $(document).ready(function () {
                            if (isFavorited) {
                                $('#favorite-checkbox').prop('checked', true);
                                $('#favorite-icon').attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-filled2.png');
                            }
                        });

                        // For My E-Shelf
                        $.get("{{ route('book.eshelf.check', ['book' => $book->id]) }}")
                            .done(function (response) {
                                if (response.isMyEshelf) {
                                    isEshelfOn = true;
                                    $('#eshelf-icon').attr('src', '{{ config("app.url","/wlibrary") }}/image/button/ebook-filled.png');
                                    $('#eshelf-checkbox').prop('checked', true);
                                }
                            });

                        $(document).ready(function () {
                            if (isEshelfOn) {
                                $('#eshelf-checkbox').prop('checked', true);
                                $('#eshelf-icon').attr('src', '{{ config("app.url","/wlibrary") }}/image/button/ebook-filled.png');
                            }
                        });

                        function toggleFavorite(checkbox) {
                            let isChecked = checkbox.checked;
                            let icon = $('#favorite-icon');
                            if (isChecked) { // try to add it as favorite
                                icon.attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-filled2.png');
                                $.post("{{ route('book.favorite.store', ['book' => $book->id]) }}", {
                                    _token: '{{ csrf_token() }}'
                                });

                            } else { // try to remove favorite
                                icon.attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-empty2.png');
                                $.ajax({
                                    url: "{{ route('book.favorite.remove', ['book' => $book->id]) }}",
                                    type: 'DELETE',
                                    data: { _token: '{{ csrf_token() }}' }
                                });
                            }
                        }

                        function toggleEshelf(checkbox) {
                            let isChecked = checkbox.checked;
                            let icon = $('#eshelf-icon');
                            if (isChecked) { // try to add it as my eshelf book
                                icon.attr('src', '{{ config("app.url","/wlibrary") }}/image/button/ebook-filled.png');
                                $.post("{{ route('book.eshelf.store', ['book' => $book->id]) }}", {
                                    _token: '{{ csrf_token() }}'
                                });

                            } else { // try to remove my eshelf book
                                icon.attr('src', '{{ config("app.url","/wlibrary") }}/image/button/ebook-empty.png');
                                $.ajax({
                                    url: "{{ route('book.eshelf.remove', ['book' => $book->id]) }}",
                                    type: 'DELETE',
                                    data: { _token: '{{ csrf_token() }}' }
                                });
                            }
                        }
                    </script>
                </span>
            @endif
        </div>
        
        <!-- Reading Start Modal -->
        <div class="modal fade" id="readingStartModal" tabindex="-1" role="dialog" aria-labelledby="readingStartModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="readingStartModalLabel">
                            <i class="fas fa-book-open text-success mr-2"></i>독서 시작
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="mb-0">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        </p>
                        <h5 class="mt-3">e-shelf에 담겼습니다.</h5>
                        <p class="text-muted">독서를 시작하시겠습니까?</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">아니오</button>
                        <button type="button" class="btn btn-success" onclick="startReading()">
                            <i class="fas fa-play mr-1"></i>네, 시작합니다
                        </button>
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
                        <h5 class="mt-3 text-warning">All reading history will be deleted</h5>
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
            <div class="card-body col-12">
                <form method="POST" name='form1' id='pform' action="{{config('app.url','/wlibrary')."/book/".$book->id}}" enctype="multipart/form-data">
                    @csrf 
                    <input type='hidden' name='_method' value='PUT'>
                    <input type='hidden' name="progress_up_flag">                   
                    <input type='hidden' name="id" value='{{$book->id}}'>
                    <input type='hidden' name="del_file">                   
                    <div class="form-group row">
                        <label for="title" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['title'] }}</label>

                        <div class="col-md-9">
                            <div class='container border-0 mt-0'>{{ $book->title }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="author" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['author'] }}</label>

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
                            $tocJson = old('auto_toc', $book->auto_toc ?? '[]');
                            $toc = is_array($tocJson) ? $tocJson : json_decode($tocJson, true) ?? [];

                            // Group: level 1 = chapters; attach following items with level > 1 as children until next level 1
                            $chapters = [];
                            $current = null;

                            foreach ($toc as $item) {
                                $item = [
                                    'title' => $item['title'] ?? 'Untitled',
                                    'page'  => $item['page'] ?? null,   // legacy single page
                                    'start' => $item['start'] ?? ($item['page'] ?? null),
                                    'end'   => $item['end']   ?? ($item['page'] ?? null),
                                    'level' => $item['level'] ?? 1,
                                ];

                                if ($item['level'] <= 1) {
                                    if ($current) $chapters[] = $current;

                                    $current = [
                                        'title'    => $item['title'],
                                        'page'     => $item['page'],
                                        'start'    => $item['start'],
                                        'end'      => $item['end'],
                                        'children' => [],
                                    ];
                                } else {
                                    if (!$current) { // in case JSON starts with > level 1
                                        $current = [
                                            'title'    => 'Chapter',
                                            'page'     => null,
                                            'start'    => null,
                                            'end'      => null,
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
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
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
                                $hasChildren = !empty($ch['children']);
                            @endphp

                            <div class="card">
                                <div class="card-header" id="{{ $headingId }}">
                                    <h5 class="mb-0 d-flex align-items-center justify-content-between">     
                                        {{-- If chapter has children, make title clickable to expand --}}
                                        @if($hasChildren)
                                            <button class="btn btn-link" type="button"  
                                                    data-toggle="collapse"
                                                    data-target="#{{ $collapseId }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="{{ $collapseId }}"
                                                    data-start="{{ $startPage }}"
                                                    data-end="{{ $endPage }}"
                                                    data-rf="{{ e($rfiles[0]) }}"
                                                    data-rid="{{ e($book->rid) }}">
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
                                            <span class="btn" style="pointer-events: none; cursor: default;">
                                                {{ $ch['title'] }}
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
                                                    data-start="{{ $startPage }}"
                                                    data-end="{{ $endPage }}"
                                                    data-rf="{{ e($rfiles[0]) }}"
                                                    data-rid="{{ e($book->rid) }}"
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
                                                    @endphp
                                                    <li class="list-group-item d-flex align-items-center justify-content-between">
                                                        <span>
                                                            @if(($child['level'] ?? 2) > 2)
                                                                <span class="text-muted mr-2" style="display:inline-block; width: {{ (($child['level']-2)*14) }}px;"></span>
                                                            @endif
                                                            {{ $child['title'] }}
                                                        </span>

                                                        @if(!is_null($child['page']))
                                                            <button type="button"
                                                                    class="btn btn-outline-secondary btn-sm"
                                                                    data-start="{{ $cStart }}"
                                                                    data-end="{{ $cEnd }}"
                                                                    data-rf="{{ e($rfiles[0]) }}"
                                                                    data-rid="{{ e($book->rid) }}"
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
                        <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_rtype'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_rtype',$book->c_rtype)?></div>
                        </div>
                    </div>

                @if(\vwmldbm\code\is_code_usable('code_c_genre'))    
                    <div class="form-group row">
                        <label for="c_genre" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_genre'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_genre',$book->c_genre)?></div>
                        </div>
                    </div>
                @endif

                @if(\vwmldbm\code\is_code_usable('code_c_grade'))
                    <div class="form-group row">
                        <label for="c_grade" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_grade'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_grade',$book->c_grade)?></div>
                        </div>
                    </div>
                @endif

                @if(\vwmldbm\code\is_code_usable('code_c_category'))
                    <div class="form-group row">
                        <label for="c_category" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_category'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_category',$book->c_category)?></div>
                        </div>
                    </div>
                @endif
                
                @if(\vwmldbm\code\is_code_usable('code_c_category2'))
                    <div class="form-group row">
                        <label for="c_category2" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_category2'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_category2',$book->c_category2)?></div>
                        </div>
                    </div>
                @endif

                    <div class="form-group row">
                        <label for="c_lang" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_lang'] }}</label>

                        <div class="col-md-9">
                            <span class='form-control border-0'>
                                <?=\vwmldbm\code\print_code('vwmldbm_c_lang',$book->c_lang,'c_lang',null,null,null,'RD_ONLY',null,"class='form-control'");?>
                            </span>
                        </div>
                    </div>
                    

                    <div class="form-group row">
                        <label for="e_resource_yn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['e_resource_yn'] }}</label>
                        <div class="col-md-9">
                            <div class='form-control border-0'>                            
                                <?PHP
                                echo \vwmldbm\code\print_c_yn('e_resource_yn',$book->e_resource_yn,null,'RD_ONLY',null,'Y_BLUE');
                                ?>
                            </div>                             
                        </div>
                    </div>                                    

                    <div class="form-group row">
                        <label for="desc" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['desc'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control-static overflow-auto' style='max-height:600px;min-height:100px;'>
                                <?=stripslashes($book->desc)?>
                            </div>                         
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="publisher" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['publisher'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->publisher }}</div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="url" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['url'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><a href='{{ $book->url }}' target='_blank'>{{ $book->url }}</a></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="pub_date" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['pub_date'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->pub_date }}</div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="isbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['isbn'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->isbn }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="eisbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['eisbn'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->eisbn }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="keywords" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['keywords'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->keywords }}</div>                               
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="price" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['price'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->price }}</div>                               
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="cover_image" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['cover_image'] }}</label>
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

    <div class="container col-12"> 
        <table class="table table-striped table-responsive-md">
        <tr>
            <th> </th>
            <th>{{$field_arr2["barcode"]}}</th>
            <th>{{$field_arr2["call_no"]}}</th>
            <th>{{$field_arr2["location"]}}</th>
            <th>{{$field_arr2["c_rstatus"]}}</th>
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
<br><br>
@endsection