<?php
    $isAdmin = (Auth::check() && Auth::user()->isAdmin()) ? true : false;
    $perm['R'] ='Y'; // Read permission
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

// Bilingual Reader: share the same endpoint as the working Book page.
$readerTranslationUrl = route('book.bilingual.translate', ['book' => $book->id]);
$readerCurrentLocale = app()->getLocale();
$readerManagedLang = \vwmldbm\code\manage_lang($readerCurrentLocale, 'ccode');
?>

@extends('layouts.root')
@section('content')
<!-- FontAwesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Book Show CSS -->
<link rel="stylesheet" href="{{ auto_asset('css/book.css') }}">

<style>
.reading-title {
    font-family: 'Georgia', 'Times New Roman', serif; /* elegant serif font */
    font-size: 1.2rem;
    font-weight: bold;
    color: #333;

    background-color: #fff8dc;     /* light golden background */
    border: 2px solid #c9a600;     /* dark yellow frame */
    border-radius: 8px;            /* rounded corners */
    padding: 12px 20px;

    display: inline-block;         /* shrink frame around text */
    margin: 0 auto;                /* center horizontally */
    box-shadow: 2px 2px 6px rgba(0,0,0,0.2); /* subtle shadow */
}

</style>

<style>
/* For summary button and modal */
/* Olive button */
.btn-olive {
  background-color: #6b8e23; /* olive drab */
  border-color: #6b8e23;
  color: #fff;
}
.btn-olive:hover,
.btn-olive:focus {
  background-color: #5f801f;
  border-color: #5f801f;
  color: #fff;
}

/* Bottom sheet inside the fullscreen modal */
#summarySheetBackdrop {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,.35);
  display: none;
  z-index: 1059; /* just under the sheet itself */
}

#summarySheet {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 50vh;       /* half the fullscreen modal */
  max-height: 65vh;   /* safety cap */
  background: #fff;
  border-top-left-radius: 14px;
  border-top-right-radius: 14px;
  box-shadow: 0 -8px 24px rgba(0,0,0,.25);
  transform: translateY(100%);
  transition: transform .28s ease;
  z-index: 1060; /* above iframe/body inside modal */
  display: flex;
  flex-direction: column;
}

#summarySheet.show {
  transform: translateY(0);
}

#summarySheet .sheet-handle {
  width: 48px;
  height: 5px;
  border-radius: 999px;
  background: #ddd;
  margin: 10px auto 6px;
}

#summarySheetHeader {
  padding: 6px 14px 8px;
  border-bottom: 1px solid #eee;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

#summarySheetBody {
  padding: 12px 14px;
  overflow: auto;
}

#summaryText {
  width: 100%;
  min-height: 28vh;
  resize: vertical;
}

#summarySheetFooter {
  padding: 10px 14px 14px;
  border-top: 1px solid #f1f1f1;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

/* for summary
/* Bootstrap 4-safe custom badge color */
.badge.badge-olive {
  background-color: #6b8e23 !important;
  color: #fff !important;
  /* Optional: ensure it's visually like other badges */
  border: none !important;
}

/* If the badge is ever a link */
a.badge.badge-olive:hover,
a.badge.badge-olive:focus {
  color: #fff !important;
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

    // console.log("Go to page:", "idx", idx, "Page:", page, "Start:", start, "End:", end, "RF:", rf, "RID:", rid, "Status:", status, "start_time", start_time, "end_time", end_time); // debugging

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

        document.getElementById('fullscreenModalLabel').innerHTML +=
            " <button id='openSummaryBtn' type='button' class='btn btn-olive btn-sm ml-2' onclick='openSummarySheet()'>" +
            "   <i class=\"fas fa-pen mr-1\"></i>{{ __('Write Summary') }}" +
            " </button>";

      @endif

        // Add the v4 bilingual selector next to the existing AI icon only after
        // the Start/Finish/Reset/Summary buttons have been rendered. This avoids
        // losing the selector when the legacy code uses innerHTML +=.
        const aiTrigger = document.querySelector('#fullscreenModalLabel img[data-target="#aiModal"]');
        if (aiTrigger) {
        const langSelect = document.createElement('select');
        langSelect.id = 'bilingualTargetLanguage';
        langSelect.className = 'custom-select custom-select-sm ml-2';
        langSelect.style.cssText = 'width:auto;max-width:220px;vertical-align:middle;font-size:0.85rem;';
        langSelect.setAttribute('aria-label', 'Choose bilingual translation language');
        const languageChoices = [
          ['', '🌐 Bilingual read…'],
          ['id', 'Indonesian / Bahasa Indonesia'],
          ['ko', 'Korean / 한국어'],
          ['en', 'English'],
          ['ar', 'Arabic / العربية'],
          ['fr', 'French / Français'],
          ['es', 'Spanish / Español'],
          ['vi', 'Vietnamese / Tiếng Việt'],
          ['ru', 'Russian / Русский']
        ];
        languageChoices.forEach(([code, label]) => {
          const option = document.createElement('option');
          option.value = code;
          option.textContent = label;
          langSelect.appendChild(option);
        });
        // Put the current display language first, but do not preselect it:
        // selecting Indonesian must still fire "change" when the UI is Indonesian.
        const currentUiLang = String(@json($readerCurrentLocale)).toLowerCase().split(/[-_]/)[0];
        const preferredOption = Array.from(langSelect.options).find(o => o.value === currentUiLang);
        if (preferredOption) langSelect.insertBefore(preferredOption, langSelect.options[1]);
        langSelect.title = 'Choose a bilingual language (current display language: ' + currentUiLang + ')';
        aiTrigger.insertAdjacentElement('afterend', langSelect);
        }
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

<script>
  // For summary bottom sheet
  // Open the sheet: pulls current start/end from the fullscreenModal data set in goToPage()
  function openSummarySheet() {
    const $modal = $('#fullscreenModal');
    const data   = $modal.data() || {};
    const start  = data.start || data.page || '';
    const end    = data.end   || data.page || '';
    const rid    = data.rid;

    // label
    const rangeText = (String(start) === String(end)) ? ('p. ' + start) : ('pp. ' + start + '–' + end);
    document.getElementById('summaryPageRange').textContent = rangeText;

    const key = summaryKey(rid, start, end);
    const cached = summaryCache.get(key);

    // If we have a cached draft (dirty or not), use it and don't fetch (unless marked stale and not dirty)
    if (cached) {
      $('#summaryText').val(cached.text || '');
      // If stale but not dirty, refresh from server in the background and only replace if user hasn't typed meanwhile.
      if (cached.stale && !cached.dirty) {
        const token = Date.now().toString();
        $('#summaryText').data('fetch-token', token);

        $.get("{{ route('reading_history.section_summary.get', ['book' => $book->id]) }}", { start, end })
          .done(function(res){
            const txt = (res && typeof res.summary === 'string') ? res.summary : '';
            const current = summaryCache.get(key) || { text:'', dirty:false, stale:false };
            // Only update if still not dirty and token matches (user didn't type)
            if (!current.dirty && $('#summaryText').data('fetch-token') === token) {
              summaryCache.set(key, { text: txt, dirty: false, stale: false });
              $('#summaryText').val(txt);
            }
          })
          .always(function(){
            $('#summaryText').removeData('fetch-token');
          });
      }

      showSummarySheetUI();
      return;
    }

    // No cache: set empty while fetching first time
    $('#summaryText').val('');
    const token = Date.now().toString();
    $('#summaryText').data('fetch-token', token);

    $.get("{{ route('reading_history.section_summary.get', ['book' => $book->id]) }}", { start, end })
      .done(function(res){
        const txt = (res && typeof res.summary === 'string') ? res.summary : '';
        // set as fresh (not dirty, not stale)
        summaryCache.set(key, { text: txt, dirty: false, stale: false });
        // only write if same request
        if ($('#summaryText').data('fetch-token') === token) {
          $('#summaryText').val(txt);
        }
      })
      .fail(function(){
        summaryCache.set(key, { text: '', dirty: false, stale: false });
      })
      .always(function(){
        $('#summaryText').removeData('fetch-token');
      });

    showSummarySheetUI();

    function showSummarySheetUI() {
      document.getElementById('summarySheetBackdrop').style.display = 'block';
      document.getElementById('summarySheet').classList.add('show');
      document.getElementById('summarySheet').setAttribute('aria-hidden', 'false');
      setTimeout(() => document.getElementById('summaryText').focus(), 120);
    }
  }

  function closeSummarySheet() {
    document.getElementById('summarySheet').classList.remove('show');
    document.getElementById('summarySheet').setAttribute('aria-hidden', 'true');
    document.getElementById('summarySheetBackdrop').style.display = 'none';
    // NOTE: we DO NOT clear textarea here; cache already holds the current text (dirty)
  }

  // Simple draft saver (front-end only). Wire to your API later.
  function saveSummaryDraft() {
    var $modal = $('#fullscreenModal');
    var data = $modal.data() || {};
    var start = data.start || data.page || '';
    var end   = data.end   || data.page || '';
    var text  = $('#summaryText').val().trim();

    $.post("{{ route('reading_history.section_summary.store', ['book' => $book->id]) }}", {
        _token: "{{ csrf_token() }}",
        start: start,
        end: end,
        summary: text
    }).done(function(res){
        // toast/feedback here...
        closeSummarySheet();
    }).fail(function(xhr){
        alert('Failed to save summary: ' + (xhr.responseJSON?.error || xhr.statusText));
    });
  }


  // Optional helper if you want per-section caching
  function summaryCacheKey(rid, start, end) {
    return 'summary:' + (rid || 'rid') + ':' + (start || 's') + '-' + (end || 'e');
  }

  // Ensure sheet closes when the fullscreen modal closes
  $('#fullscreenModal').on('hidden.bs.modal', function () {
    closeSummarySheet();
  });


  // Per-section cache: key => { text: string, stale: boolean }
  const summaryCache = new Map();
  function summaryKey(rid, start, end) {
    return `${rid || 'rid'}:${start || 's'}-${end || 'e'}`;
  }
  

    // Attach once
  $(document).ready(function () {
    $('#summaryText').on('input', function () {
      const $modal = $('#fullscreenModal');
      const data   = $modal.data() || {};
      const rid    = data.rid;
      const start  = data.start || data.page || '';
      const end    = data.end   || data.page || '';
      const key    = summaryKey(rid, start, end);

      const existing = summaryCache.get(key) || { text: '', dirty: false, stale: false };
      summaryCache.set(key, { text: this.value, dirty: true, stale: existing.stale });
    });
  });

   // Called by the summary badge
  function openSectionAndSummary(btn) {
    // mark this click as "also open the summary editor"
    btn.setAttribute('data-open-summary', '1');
    goToPage(btn);
  }
</script>

<!-- top icons and buttons -->
<div class="container mt-0">
    <div class="row">
        <div class="col-12">
            <div class="book-content-wrapper">
                <div class="book-header d-flex justify-content-start align-items-center">

                @if(Auth::check() && !$isAdmin)
                    <!-- Reading Time Information -->
                    @if($isEresource)
                      <label>
                        @php
                            if(empty($readingHistory) || $readingHistory->startable()) {
                                $sButtonDisplay="inline";
                                $sButtonEnalbed = "";
                            }
                            else {
                                $sButtonDisplay="none";
                                $sButtonEnalbed = "disabled";
                            }
                        @endphp       
                        
                        <button type="button" style="display:{{$sButtonDisplay}};" id="startReadingButton" {{$sButtonEnalbed}}
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
                    
                      <!-- My Reading History Button and Modal -->
                      <label>
                        <a href="{{ route('book.show', ['book' => $book->id]) }}" class="resource-link">
                            <button type="button" id="myReadingButton"
                                class="btn btn-sm btn-primary ml-2" style="background-color: #F0F; color: #fff;">
                                {{ __('Back to Book Main Page') }}
                            </button>
                        </a>
                      </label>

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
                                if(confirm("{{__("Do you want to reset reading history of the book? All reading history except summary will be lost!")}}")) {
                                    $.ajax({
                                        url: "{{ route('reading_history.reset', ['book' => $book->id]) }}",
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

                @endif
            </div> <!-- end of <div class="book-content-wrapper"> -->
        </div> 
    </div>
</div>
<!-- end of top icons and buttons -->

<!-- Small Modals -->  
<div class="container mt-0">
    <div class="row">
        <div class="col-12">     
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
          <!-- End of Reset Warning Modal -->
        </div>
    </div>
</div>
<!-- End of Small Modals -->

<!-- Book Body -->
<div class="container mt-0">
  <form method="POST" name='form1' id='pform' action="{{config('app.url','/mindbridger')."/book/".$book->id}}" enctype="multipart/form-data">
    @csrf 
    <input type='hidden' name='_method' value='PUT'>
    <input type='hidden' name="progress_up_flag">                   
    <input type='hidden' name="id" value='{{$book->id}}'>
    <input type='hidden' name="del_file">  
    <div class="book-body">
        <div class="card-body col-12">
            <div class="d-flex justify-content-center">
                <h2 class="reading-title mb-4">{{__("My Reading History")}}</h2>
            </div>
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
            <?php
                $userToC = !empty($tocOrigin) ? true : false; // whether ToC data exists from user's reading history
                $bookToC = !empty($book->auto_toc) ? true : false; 

                $theToC = $userToC ? $tocOrigin : ($bookToC ? $book->auto_toc : null);
            ?>

            @if($userToC || $bookToC)
            <?php // if there is ToC data of the user's reading history?>

                <div class="form-group row">
                    <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">
                        {{ __("Table of Contents") }}
                    </label>

                    <div class="col-md-9">
                        <div class="accordion">
                            @php
                            // Get ToC from old input or model
                                $tocJson = $theToC ?? [];

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
                                    'summary' => $item['summary'] ?? '',
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
                                        'summary' => $item['summary'] ?? '',
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
                        </div> <!-- end of accordion -->
                        
                        <!-- Fullscreen Modal: For the section of book -->
                        <div class="modal fade edge-to-edge" id="fullscreenModal" tabindex="-1" role="dialog"
                            aria-labelledby="fullscreenModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-fullscreen" role="document">
                                <div class="modal-content">
                                    <div class="modal-header py-2">
                                        <h5 class="modal-title" id="fullscreenModalLabel">Document Viewer</h5>
                                        <button type="button " class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times; </span>
                                        </button>
                                    </div>

                                    <div class="modal-body p-0">
                                        <!-- PDF.js iframe -->
                                        <iframe id="pdfIframe" title="PDF Viewer"
                                            style="width:100%; height:100%; border:0;" allow="fullscreen">
                                        </iframe>

                                        <!-- Bottom-sheet summary editor (lives inside fullscreenModal) -->
                                        <div id="summarySheetBackdrop" onclick="closeSummarySheet()"></div>

                                        <div id="summarySheet" aria-hidden="true">
                                            <div class="sheet-handle" role="presentation"></div>

                                            <div id="summarySheetHeader">
                                                <div>
                                                    <i class="fas fa-pen mr-2 text-muted"></i>
                                                    <strong>{{ __('Page Summary') }}</strong>
                                                    <small id="summaryPageRange" class="text-muted ml-2"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeSummarySheet()">
                                                    {{ __('Close') }}
                                                </button>
                                            </div>

                                            <div id="summarySheetBody">
                                                <label for="summaryText" class="sr-only">{{ __('Write summary') }}</label>
                                                <textarea id="summaryText" class="form-control" placeholder="{{ __('Write a brief summary for this section/page...') }}"></textarea>
                                                <small id="summaryHelper" class="form-text text-muted mt-1">
                                                {{ __('Tip: Capture the main idea, key terms, and 1–2 insights.') }}
                                                </small>
                                            </div>

                                            <div id="summarySheetFooter">
                                                <button type="button" class="btn btn-light" onclick="closeSummarySheet()">{{ __('Cancel') }}</button>
                                                <button type="button" class="btn btn-success" onclick="saveSummaryDraft()">
                                                <i class="fas fa-save mr-1"></i>{{ __('Save Draft') }}
                                                </button>
                                            </div>
                                        </div>
                                        <!-- End of Bottom-sheet summary editor (lives inside fullscreenModal) -->                          
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Fullscreen Modal: For the section of book -->

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
                                    $summary = !empty($ch['summary']);
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
                                                data-end_time="{{ $end_time }}"
                                                data-summary="{{ $summary }}"
                                            >

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
                                           $summaryBadge = (!empty($ch['summary']))
                                            ? "<span class='badge badge-olive mr-1' data-toggle='tooltip' title='".__('Summary')."'>
                                                <i class='fas fa-file-lines'></i>
                                                <span class='sr-only'>".__('Summary')."</span>
                                                </span>"
                                            : "";

                                            if($ch['status'] == 'in_progress') {
                                                $badge = "<span class='badge badge-warning'>".__("Reading..")."</span>";
                                            } else if($ch['status'] == 'completed') {
                                                $badge = "<span class='badge badge-success'>".__("Done :)")."</span>";
                                            } else {
                                                $badge ="";
                                            }
                                            ?>
                                            <span class="btn" style="pointer-events: none; cursor: default;">
                                                {!! $summaryBadge !!} {!! $badge !!} {{ $ch['title'] }}
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
                                                data-summary="{{ $summary }}"
                                                onclick="goToPage(this)"
                                            >
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

                                            $summaryBadge = (!empty($child['summary']))
                                                ? "<span class='badge badge-olive mr-1' data-toggle='tooltip' title='".__('Summary')."'>
                                                    <i class='fas fa-file-lines'></i>
                                                    <span class='sr-only'>".__('Summary')."</span>
                                                    </span>"
                                                : "";
                                                
                                            if($child['status'] == 'in_progress') {
                                                $badge = "<span class='badge badge-warning'>".__("Reading..")."</span>";
                                            } else if($child['status'] == 'completed') {
                                                $badge = "<span class='badge badge-success'>".__("Done :)")."</span>";
                                            } else $badge ="";
                                            @endphp

                                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>
                                                @if(($child['level'] ?? 2) > 2)
                                                <span class="text-muted mr-2" style="display:inline-block; width: {{ (($child['level']-2)*14) }}px;"></span>
                                                @endif
                                                {!! $summaryBadge !!} {!! $badge !!} {{ $child['title'] }}
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
                                                    data-summary="{{ $child['summary'] }}"
                                                    onclick="goToPage(this)"
                                                >
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
                            @if(true || $historyTocMode)
                                // Expand all accordion panels
                                $('#tocAccordion .collapse').each(function () {
                                    $(this).collapse('show');
                                });
                            @endif
                        });
                    </script>
                </div>

                @elseif(!empty($book->auto_toc))
                
                <?php // if there is ToC data of book?>
                <div class="form-group row">
                    <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Table of Contents") }}</label>
                    <div class="col-md-9">
                        {{ __("Book ToC.") }}
                    </div>
                </div>
               
                <?php // end of if there is ToC data of book?>

                @else  <?php // no ToC?>
                
                <div class="form-group row">
                    <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Table of Contents") }}</label>
                    <div class="col-md-9">
                        {{ __("No Table of Contents available.") }}
                    </div>
                </div>
            @endif
            <!-- End ToC Display: Accordion -->                          

                <div class="form-group row">
                    <label for="desc" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __("Description") }}</label>

                    <div class="col-md-9">
                        <div class='form-control-static overflow-auto' style='max-height:600px;min-height:100px;'>
                            <?=stripslashes($book->desc)?>
                        </div>                         
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-9 offset-md-4">                               
                        <button type="button" class="btn btn-success" onClick="window.history.back();">
                            {{ __('Go Back') }}
                        </button>
                    </div>                          
                </div>                                           
            </div>
        </div>
    </div>
  </form>

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

<!-- Bilingual Reader: uses PDF.js selectable text from the opened section. -->
<script>
(function() {
  'use strict';

  const config = {
    bookTitle: @json($book->title),
    translationUrl: @json($readerTranslationUrl),
    csrf: @json(csrf_token()),
    // The project's language helper returns the ccode. The `&& !session('refreshed')`
    // expression is a boolean and is therefore NOT a suitable language-code value.
    uiLanguage: @json($readerCurrentLocale),
    managedLanguage: @json($readerManagedLang),
  };

  const targetNames = {
    id: 'Indonesian', ko: 'Korean', en: 'English', ar: 'Arabic',
    fr: 'French', es: 'Spanish', vi: 'Vietnamese', ru: 'Russian'
  };

  let activeRun = 0;

  // Select is recreated each time the PDF fullscreen modal is shown.
  $(document).on('change', '#bilingualTargetLanguage', function() {
    const target = this.value;
    this.value = ''; // allow selecting the same language again later
    if (!targetNames[target]) return;

    const section = $('#fullscreenModal').data();
    openBilingualReader(Number(section.start), Number(section.end), target);
  });

  function popupElement(doc, tag, className, value) {
    const el = doc.createElement(tag);
    if (className) el.className = className;
    if (value != null) el.textContent = String(value);
    return el;
  }

  // Text is always rendered using textContent, never unescaped innerHTML.
  function buildPopup(win, target, start, end) {
    const doc = win.document;
    doc.open();
    doc.write(`<!doctype html><html lang="${target}"><head><meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>MindBridger — Bilingual Reader</title>
      <style>
        body{font-family:system-ui,-apple-system,'Segoe UI',Arial,sans-serif;background:#f7f9fc;color:#1e293b;margin:0;line-height:1.65}
        header{position:sticky;top:0;background:white;border-bottom:1px solid #dce4ee;padding:15px 22px;z-index:1}
        h1{font-size:1.25rem;margin:0 0 4px}#subtitle{font-size:.9rem;color:#64748b;margin-bottom:10px}
        #printButton{float:right;padding:7px 12px;border:1px solid #94a3b8;border-radius:7px;background:white;cursor:pointer}
        #printButton:disabled{opacity:.5;cursor:not-allowed}
        main{max-width:980px;margin:20px auto;padding:0 18px 45px}
        .page{margin:28px 0 10px;font-size:1.03rem;color:#334155;border-bottom:2px solid #cbd5e1;padding-bottom:8px}
        .language-head{font-weight:700;color:#475569;padding:10px 2px}
        .pair{background:white;border:1px solid #e2e8f0;border-radius:9px;padding:12px 16px;margin:14px 0;break-inside:avoid}
        /* Sentence-by-sentence: original sentence, followed by its translation. */
        .original{display:block;white-space:pre-wrap;font-weight:600;color:#1e293b}
        .translation{display:block;white-space:pre-wrap;margin-top:3px;color:#0f766e}
        .muted{color:#64748b}.error{color:#b91c1c}.notice{margin:14px 0}
        .loading-indicator{display:flex;align-items:center;gap:10px;padding:9px 12px;background:#eff6ff;
          border-radius:8px;color:#1d4ed8;font-size:.9rem;font-weight:600;clear:both}
        .loading-indicator.done{background:#f0fdf4;color:#166534}
        .loading-indicator.failed{background:#fef2f2;color:#b91c1c}
        .spinner{display:inline-block;flex:none;width:20px;height:20px;border:3px solid #bfdbfe;
          border-top-color:#2563eb;border-radius:50%;animation:bilingual-spin .8s linear infinite}
        @keyframes bilingual-spin{to{transform:rotate(360deg)}}
        .loading-indicator.done .spinner,.loading-indicator.failed .spinner{display:none}
        .loading-placeholder{padding:42px 15px;text-align:center;color:#475569;background:white;
          border:1px dashed #cbd5e1;border-radius:10px;margin-top:18px}
        .loading-placeholder .spinner{width:34px;height:34px;border-width:4px;vertical-align:middle;margin-right:10px}
        .loading-placeholder[hidden]{display:none}
        @media(max-width:680px){header{padding:12px}main{padding:0 10px 28px}.pair{padding:10px 12px}}
        @media print{header{position:static}#printButton,.loading-indicator,.loading-placeholder{display:none}
          body{background:white}.pair{break-inside:avoid}}
      </style></head><body>
      <header><button id="printButton" type="button" disabled>Print / Save PDF</button>
      <h1 id="bookTitle"></h1><div id="subtitle"></div>
      <div id="loadingIndicator" class="loading-indicator" role="status" aria-live="polite">
        <span class="spinner" aria-hidden="true"></span><span id="progress">Preparing PDF text…</span>
      </div></header>
      <main><div class="language-head"><span>Original PDF text ↓ </span><span id="targetHeading"></span></div>
        <div id="loadingPlaceholder" class="loading-placeholder" role="status">
          <span class="spinner" aria-hidden="true"></span><span>Reading PDF and generating bilingual text…</span>
        </div><div id="readerContent"></div></main>
      </body></html>`);
    doc.close();
    doc.getElementById('bookTitle').textContent = config.bookTitle;
    doc.getElementById('subtitle').textContent = 'MindBridger · Pages ' + start + '–' + end;
    doc.getElementById('targetHeading').textContent = targetNames[target] + ' · AI translation';
    doc.getElementById('printButton').addEventListener('click', () => win.print());
    return doc;
  }

  function readerStatus(doc, message, state='loading') {
    const status = doc.getElementById('loadingIndicator');
    status.classList.toggle('done', state === 'done');
    status.classList.toggle('failed', state === 'failed');
    doc.getElementById('progress').textContent = message;
    if (state !== 'loading') {
      doc.getElementById('loadingPlaceholder').hidden = true;
      doc.getElementById('printButton').disabled = state !== 'done';
    }
  }

  function addNotice(doc, container, text, className='notice muted') {
    container.appendChild(popupElement(doc, 'p', className, text));
  }

  async function getPDFDocument() {
    const iframe = document.getElementById('pdfIframe');
    // Works only if PDF.js viewer is served from the same origin as MindBridger.
    for (let attempt = 0; attempt < 40; attempt++) {
      const app = iframe.contentWindow && iframe.contentWindow.PDFViewerApplication;
      if (app && app.pdfDocument) return app.pdfDocument;
      await new Promise(resolve => setTimeout(resolve, 250));
    }
    throw new Error('PDF.js text is not ready. Open the PDF and wait for it to finish loading.');
  }

  async function extractLines(pdf, pageNumber) {
    const page = await pdf.getPage(pageNumber);
    const content = await page.getTextContent();
    const lines = [];
    let current = '';

    content.items.forEach(item => {
      const part = String(item.str || '');
      if (part) {
        // Most PDF.js text items include explicit whitespace, but insert a space
        // between consecutive word-like chunks when it is missing.
        const needsSpace = current && !/\s$/.test(current) && !/^\s/.test(part)
          && !/^[,.;:!?%)\]、。]/.test(part) && !/[(\[]$/.test(current);
        current += (needsSpace ? ' ' : '') + part;
      }
      if (item.hasEOL) {
        lines.push(current.trim());
        current = '';
      }
    });
    if (current.trim()) lines.push(current.trim());
    return lines;
  }

  // Reassemble headings and discard only HIGH-CONFIDENCE OCR/diagram noise
  // before sending text to the translation API. Visual PDF lines are not sentences.
  function isLikelyPdfNoise(text) {
    const s = String(text || '').trim();
    if (s.length < 35) return false; // Leave short fragments to AI for context.
    const chars = Array.from(s);
    const punctuation = chars.filter(ch => /[^\p{L}\p{N}\s]/u.test(ch)).length;
    const words = chars.filter(ch => /[\p{L}\p{N}]/u.test(ch)).length;
    const singles = (s.match(/(?:^|[^A-Za-z])[A-Za-z](?=$|[^A-Za-z])/g) || []).length;
    const punctRate = punctuation / chars.length;
    const textRate = words / chars.length;

    // Typical corrupt diagram text: scattered one-letter fragments and punctuation.
    if (singles >= 7 && punctuation >= 12 && punctRate >= 0.20) return true;
    // Especially broken OCR glyph streams, but not ordinary prose or clean captions.
    if (s.length >= 65 && punctuation >= 22 && punctRate > 0.38 && textRate < 0.43) return true;
    if ((s.match(/\uFFFD/g) || []).length >= 4 && punctuation >= 10) return true;
    return false;
  }

  function sentencesFromLines(lines) {
    const paragraphs = [];
    let current = '';
    let pendingMarker = '';
    const flush = () => {
      if (current.trim()) paragraphs.push(current.trim());
      current = '';
    };
    // Roman numeral / alphabet / list markers are chapter labels, not sentences.
    // Only recognize a bare marker on its OWN PDF line.
    const standaloneMarker = /^(?:\(?[IVXLCDM]{1,8}\)?|[A-Z]|\d{1,2})(?:[.)])?$/u;
    const inlineHeading = /^(?:\(?[IVXLCDM]{1,8}\)?|[A-Z]|\d{1,2})[.)]\s+\S/u;
    const conventionalHeading = /^(?:chapter|unit|lesson|section)\s+\d+\b/i;

    lines.forEach(raw => {
      const line = String(raw || '').replace(/\s+/g, ' ').trim();
      if (!line) { flush(); return; }
      if (isLikelyPdfNoise(line)) {
        // Discard noise without attaching it to neighboring prose.
        flush();
        return;
      }
      if (standaloneMarker.test(line)) {
        flush();
        pendingMarker = line;
        return;
      }
      if (pendingMarker) {
        flush();
        // Join 'II.' followed by '근원적 종교개혁' as ONE title segment.
        // A marker lacking a usable next line is not translated on its own.
        paragraphs.push(pendingMarker + ' ' + line);
        pendingMarker = '';
        return;
      }
      const isHeading = line.length <= 130 && (
        inlineHeading.test(line) || conventionalHeading.test(line) ||
        /^[A-Z][A-Z\d \t:,-]{5,}$/.test(line)
      );
      if (isHeading) {
        flush();
        paragraphs.push(line);
        return;
      }
      // Rejoin a word hyphenated only because it wrapped in the PDF.
      if (current.endsWith('-') && /^[a-z]/.test(line)) {
        current = current.slice(0, -1) + line;
      } else {
        current = current ? current + ' ' + line : line;
      }
    });
    flush();

    const segments = [];
    paragraphs.forEach(paragraph => {
      if (isLikelyPdfNoise(paragraph)) return;
      // A heading is displayed/translated as a whole, never split at 'II.'.
      const isHeading = paragraph.length <= 130 && (
        inlineHeading.test(paragraph) || conventionalHeading.test(paragraph) ||
        /^[A-Z][A-Z\d \t:,-]{5,}$/.test(paragraph)
      );
      if (isHeading) {
        segments.push(paragraph);
        return;
      }
      let parts;
      if (typeof Intl !== 'undefined' && typeof Intl.Segmenter === 'function') {
        parts = [...new Intl.Segmenter('en', {granularity: 'sentence'}).segment(paragraph)]
          .map(item => item.segment.trim()).filter(Boolean);
      } else {
        parts = paragraph.split(/(?<=[.!?。！？]["'”’)?\]]?)\s+(?=[^\s])/u).filter(Boolean);
      }
      const sentences = [];
      let pending = '';
      parts.forEach(part => {
        pending = pending ? pending + ' ' + part : part;
        if (/\b(?:Mr|Mrs|Ms|Dr|Prof|Sr|Jr|St|Rev|Gen|Col|Capt|Lt|Mt|No|Fig)\.$/i.test(pending)) return;
        sentences.push(pending);
        pending = '';
      });
      if (pending) sentences.push(pending);

      sentences.forEach(sentence => {
        if (isLikelyPdfNoise(sentence)) return;
        while (sentence.length > 1000) {
          let cut = sentence.lastIndexOf(' ', 1000);
          if (cut < 500) cut = 1000;
          segments.push(sentence.slice(0, cut).trim());
          sentence = sentence.slice(cut).trim();
        }
        if (sentence) segments.push(sentence);
      });
    });
    return segments;
  }

  function batchSegments(segments) {
    const batches = [];
    let batch = [], length = 0;
    segments.forEach(text => {
      if (batch.length >= 24 || (length + text.length > 4000 && batch.length)) {
        batches.push(batch); batch = []; length = 0;
      }
      batch.push(text); length += text.length;
    });
    if (batch.length) batches.push(batch);
    return batches;
  }

  async function translateBatch(batch, target, start, end) {
    const response = await fetch(config.translationUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': config.csrf
      },
      body: JSON.stringify({
        target_language: target, start_page: start, end_page: end, segments: batch
      })
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || data.error || ('Translation request failed (' + response.status + ')'));
    if (!Array.isArray(data.translations) || data.translations.length !== batch.length) {
      throw new Error('The translation response did not match the requested sentences.');
    }
    return data.translations;
  }

  function appendRows(doc, container, sourceSegments, translations, target) {
    let displayed = 0;
    sourceSegments.forEach((source, index) => {
      const translated = translations[index] || {};
      // AI flags illegible OCR/diagram fragments. Do not render either side.
      if (translated.skip === true) return;
      const targetText = translated.already_target ? source : (translated.translated || '');
      if (!String(targetText).trim()) return;
      const row = popupElement(doc, 'div', 'pair');
      row.appendChild(popupElement(doc, 'div', 'original', source));
      const translation = popupElement(doc, 'div', 'translation', targetText);
      translation.setAttribute('lang', target);
      row.appendChild(translation);
      container.appendChild(row);
      displayed++;
    });
    return displayed;
  }

  // The ToC can use logical/printed page numbers, while PDF.js always uses
  // 1-based physical page indices. Some customized viewers also expose only
  // the requested PDF excerpt. Resolve the mapping before extracting text.
  async function resolveSectionPages(pdf, start, end) {
    if (!Number.isInteger(start) || !Number.isInteger(end) || start < 1 || end < start) {
      throw new Error('Invalid Table of Contents page numbers: ' + start + '–' + end + '.');
    }
    if (end - start + 1 > 40) {
      throw new Error('Select a section of at most 40 pages to limit processing and translation costs.');
    }

    const total = Number(pdf.numPages);
    if (!Number.isInteger(total) || total < 1) {
      throw new Error('PDF.js did not report a valid page count. Check that the PDF finished loading.');
    }
    console.info('MindBridger bilingual page mapping', {requestedStart: start, requestedEnd: end, pdfPages: total});

    const requested = Array.from({length: end - start + 1}, (_, i) => start + i);
    if (end <= total) {
      return {pages: requested.map(n => ({bookPage: n, pdfPage: n})), note: null};
    }

    // PDF page labels can be different from physical page indices.
    // Example: a one-page PDF whose printed page label is "35".
    let labels = null;
    try {
      if (typeof pdf.getPageLabels === 'function') labels = await pdf.getPageLabels();
    } catch (e) {
      console.warn('Could not retrieve PDF page labels:', e);
    }
    if (Array.isArray(labels) && labels.length === total) {
      const matches = requested.map(n => labels.findIndex(label => {
        const normalized = String(label || '').trim();
        return normalized === String(n) ||
          new RegExp('^(?:page\\s*)?0*' + n + '$', 'i').test(normalized);
      }));
      if (matches.every((index, i) => index >= 0 && (i === 0 || index > matches[i - 1]))) {
        return {
          pages: requested.map((n, i) => ({bookPage: n, pdfPage: matches[i] + 1})),
          note: 'Using PDF page labels to match the book’s printed page numbering.'
        };
      }
    }

    // Some customized PDF.js deployments load an excerpt consisting only of
    // the selected pages. Only use an excerpt mapping when the iframe URL
    // explicitly carries this exact requested start/end and page counts match.
    const iframe = document.getElementById('pdfIframe');
    const viewerUrl = new URL(iframe.getAttribute('src') || iframe.src, window.location.href);
    const viewerStart = Number(viewerUrl.searchParams.get('start'));
    const viewerEnd = Number(viewerUrl.searchParams.get('end'));
    const isSelectedExcerpt = viewerStart === start && viewerEnd === end &&
      total === requested.length && start > total;

    if (isSelectedExcerpt) {
      return {
        pages: requested.map((n, i) => ({bookPage: n, pdfPage: i + 1})),
        note: 'PDF.js exposes ' + total + ' page(s) for the selected book range ' +
          start + '–' + end + '. Treating them as a selected-page excerpt; verify the original text matches the intended section.'
      };
    }

    throw new Error('Table of Contents requests page ' + start + (end !== start ? '–' + end : '') +
      ', but the loaded PDF contains only ' + total + ' physical page(s). ' +
      'Check the PDF file and ToC numbering. The reader will not translate an uncertain page.');
  }

  async function openBilingualReader(start, end, target) {
    // Must be called immediately from the select change event to avoid popup blockers.
    const win = window.open('', 'mindbridgerBilingualReader', 'width=1250,height=850,scrollbars=yes,resizable=yes');
    if (!win) { alert('Please allow pop-ups for MindBridger to open the bilingual reader.'); return; }
    win.focus();
    const doc = buildPopup(win, target, start, end);
    const container = doc.getElementById('readerContent');
    const placeholder = doc.getElementById('loadingPlaceholder');
    const myRun = ++activeRun;

    try {
      readerStatus(doc, 'Loading PDF text…');
      const pdf = await getPDFDocument();
      const mapping = await resolveSectionPages(pdf, start, end);
      if (mapping.note) addNotice(doc, container, mapping.note, 'notice muted');
      let translatedCount = 0;
      let skippedCount = 0;
      for (const {bookPage, pdfPage} of mapping.pages) {
        if (myRun !== activeRun || win.closed) return;
        readerStatus(doc, 'Extracting text from book page ' + bookPage + '…');
        const lines = await extractLines(pdf, pdfPage);
        const segments = sentencesFromLines(lines);
        container.appendChild(popupElement(doc, 'h2', 'page', 'Page ' + bookPage));
        if (!segments.length) {
          addNotice(doc, container, 'No selectable text found on this page (scanned PDFs require OCR).');
          continue;
        }
        let completedOnPage = 0;
        for (const batch of batchSegments(segments)) {
          if (myRun !== activeRun || win.closed) return;
          readerStatus(doc, 'Translating page ' + bookPage + ' · ' + completedOnPage +
            ' of ' + segments.length + ' sentences translated…');
          const translations = await translateBatch(batch, target, bookPage, bookPage);
          if (myRun !== activeRun || win.closed) return;
          const shown = appendRows(doc, container, batch, translations, target);
          placeholder.hidden = true; // Original/translation sentence pairs now visible.
          translatedCount += shown;
          skippedCount += batch.length - shown;
          completedOnPage += batch.length;
          readerStatus(doc, 'Translating page ' + bookPage + ' · ' + completedOnPage +
            ' of ' + segments.length + ' sentences translated…');
        }
      }
      if (myRun === activeRun && !win.closed) {
        readerStatus(doc, 'Completed · ' + translatedCount + ' bilingual sentences · ' + skippedCount + ' skipped OCR fragments · AI translation (review for accuracy)', 'done');
      }
    } catch (error) {
      if (myRun !== activeRun || win.closed) return;
      readerStatus(doc, 'Could not complete the bilingual reading.', 'failed');
      addNotice(doc, container, error.message || 'Unknown error', 'notice error');
      console.error('MindBridger bilingual reader:', error);
    }
  }
})();
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