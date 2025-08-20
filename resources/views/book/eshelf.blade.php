@extends('layouts.root')
@section('content')
<?php
    // Pre-loading the field values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);

    // Pre-loading the code values for performance
    $c_rtype_arr=array();
    \vwmldbm\code\get_code_name_all($c_rtype_arr,'code_c_rtype');
    $c_rtype_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_rtype_arr_default,'code_c_rtype',null,10);

    $c_genre_arr=array();
    \vwmldbm\code\get_code_name_all($c_genre_arr,'code_c_genre');    
    $c_genre_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_genre_arr_default,'code_c_genre',null,10);

    $c_grade_arr=array();
    \vwmldbm\code\get_code_name_all($c_grade_arr,'code_c_grade');    
    $c_grade_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_grade_arr_default,'code_c_grade',null,10);

    $c_category_arr=array();
    \vwmldbm\code\get_code_name_all($c_category_arr,'code_c_category');    
    $c_category_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_category_arr_default,'code_c_category',null,10);

    $c_category2_arr=array();
    \vwmldbm\code\get_code_name_all($c_category2_arr,'code_c_category2');    
    $c_category2_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_category2_arr_default,'code_c_category2',null,10);
    
    if(isset($books))
        $bnum=$books->total();

    $page_size=isset($_REQUEST['page_size'])? $_REQUEST['page_size'] : null;

    $search_word_arr=array();
    $operation=null;
?>

<div class="eshelf-container">
    <div class="eshelf-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="eshelf-icon me-3">
                            <img src="image/shelf.png?nocache=1" alt="e-Shelf" width="40" height="40">
                        </div>
                        <div>
                            <h1 class="eshelf-title mb-1">{{__("My e-Shelf")}}</h1>
                            <p class="eshelf-subtitle text-muted mb-0">{{__("Your personal digital library collection")}}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    @if(Auth::check() && Auth::user()->isAdmin() && !isset($no_add_btt)) 
                    <button type='button' class='btn btn-primary btn-lg' onClick="window.location='{{config('app.url','/wlibrary')}}/book/create'">
                        <i class="fas fa-plus me-2"></i>{{__("Add Book")}}
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                {!!Form::open(['method'=>'GET','class'=>'eshelf-form','name'=>'bookListForm'])!!}
                
                <!-- Page Size Selector -->
                <div class="page-controls mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="page-size-selector">
                            <label for="page_size" class="form-label fw-bold text-muted">{{__("Items per page")}}:</label>
                            <?php
                            $pageUri=null;
                            foreach($_GET as $key=>$val) {
                                if(is_array($val)) {
                                     foreach($val as $v){
                                         $pageUri.="&key[]=$v";
                                     }
                                }
                                else {
                                     $pageUri.="&$key=$val";
                                 }
                            }
                            echo \wlibrary\code\page_size('page_size',$page_size," onChange=\"window.location.href='?{$pageUri}&page_size='+this.value;\"");
                            ?>
                        </div>
                        
                        @if(isset($search_word) || $operation=='ASEARCH')
                        <div class="search-results-badge">
                            <span class="badge bg-info fs-6">{{__('Search Results')}}: {{$bnum}}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Books Table -->
                <div class="books-table-container">
                    <div class="table-responsive">
                        <table class="table table-hover eshelf-table">
                            <thead class="table-header">
                                <tr>
                                    <th class="cover-col">{{__("Cover")}}</th>
                                                                    <th class="title-col">{{__("Title")}}</th>
                                <th class="author-col">{{__("Author")}}</th>
                                <th class="type-col">{{__("E-Resource exist")}}</th>
                                <th class="rtype-col">{{__("Type")}}</th>
                                
                                @if(\vwmldbm\code\is_code_usable('c_genre'))
                                    <th class="genre-col">{{__("Genre")}}</th>
                                @endif
                                
                                @if(\vwmldbm\code\is_code_usable('c_grade'))
                                    <th class="grade-col">{{__("Grade")}}</th>
                                @endif

                                @if(\vwmldbm\code\is_code_usable('c_category'))
                                    <th class="category-col">{{__("Category")}}</th>
                                @endif

                                @if(\vwmldbm\code\is_code_usable('c_category2'))
                                    <th class="category2-col">{{__("Category2")}}</th>
                                @endif

                                <th class="publisher-col">{{__("Publisher")}}</th>
                                <th class="date-col">{{__("Pub Date")}}</th>
                                    <th class="copies-col">{{__("Copies")}}</th>
                                    
                                    @if(Auth::check() && Auth::user()->isAdmin()) 
                                    <th class="action-col"></th>
                                    <th class="action-col"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($books as $b)
                                <tr class="book-row">
                                    <td class="cover-cell">
                                        @if($b->cover_image) 
                                            <img onClick="open_cover_img(this)" class="cover-image" src='{{config('app.url','/wlibrary')}}/storage/cover_images/{{$_SESSION['lib_inst']}}/{{$b->cover_image}}' alt="Book Cover">
                                        @else
                                            <div class="no-cover-placeholder">
                                                <i class="fas fa-book text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <td class="title-cell">
                                        <a href="{{config('app.url','/wlibrary')}}/book/{{$b->id}}" class="book-title-link">
                                            <?
                                            if(isset($search_target) && $search_target=='i_title')
                                                echo \wlibrary\code\highlight($b->title,$search_word_arr);
                                            else if(!isset($search_target) && isset($search_word))
                                                echo \wlibrary\code\highlight($b->title,$search_word_arr);                           
                                            else echo $b->title;
                                            ?>
                                        </a>
                                    </td>                        
                                    
                                    <td class="author-cell">                       
                                        <?
                                        if(isset($search_target) && $search_target=='i_author')
                                            echo \wlibrary\code\highlight($b->author,$search_word_arr);
                                        else if(!isset($search_target) && isset($search_word))
                                            echo \wlibrary\code\highlight($b->author,$search_word_arr);
                                        else echo $b->author;
                                        ?>
                                    </td>
                                    
                                    <td class="type-cell">
                                        <?PHP
                                        if($b->e_resource_yn) {
                                            echo \vwmldbm\code\print_c_yn('e_resource_yn',$b->e_resource_yn,null,'RD_ONLY_Y',"");                       
                                            $rfiles=explode(';',$b->rfiles);
                                            $num_files=count($rfiles)-1; // empty entry is included
                                            if($num_files>1) $num_files_txt="({$num_files})";
                                            else $num_files_txt=null;

                                            $fsize=0;
                                            foreach($rfiles as $key => $val) {
                                                $fpath=config('app.root')."/storage/app/ebook/{$_SESSION['lib_inst']}/{$b->rid}/{$rfiles[$key]}";
                                                if($val && file_exists($fpath)) $fsize+=filesize($fpath);
                                            }
                                            $fsize=\wlibrary\code\format_fsize($fsize,'MB',1)."MB";
                                            if($num_files>0) echo "<br><span class='file-info'>{$fsize} $num_files_txt</span>";
                                        }
                                        ?>
                                    </td>

                                    <td class="rtype-cell">
                                        <?PHP
                                        if($b->c_rtype) {
                                            if(isset($c_rtype_arr[$b->c_rtype])) echo $c_rtype_arr[$b->c_rtype]; 
                                            else echo $c_rtype_arr_default[$b->c_rtype];                           
                                        }
                                        ?>
                                    </td>
                                
                                @if(\vwmldbm\code\is_code_usable('c_genre'))
                                    <td class="genre-cell">
                                        <?PHP
                                        if($b->c_genre) {
                                            if(isset($c_genre_arr[$b->c_genre])) echo $c_genre_arr[$b->c_genre]; 
                                            else echo $c_genre_arr_default[$b->c_genre];                           
                                        }
                                        ?>
                                    </td>
                                @endif

                                @if(\vwmldbm\code\is_code_usable('c_grade'))
                                    <td class="grade-cell">
                                        <?PHP
                                        if($b->c_grade) {
                                            if(isset($c_genre_arr[$b->c_grade])) echo $c_genre_arr[$b->c_grade]; 
                                            else echo $c_genre_arr_default[$b->c_grade];                           
                                        }
                                        ?>
                                    </td>
                                @endif

                                @if(\vwmldbm\code\is_code_usable('c_category'))
                                    <td class="category-cell">
                                        <?PHP
                                        if($b->c_category) {
                                            if(isset($c_category_arr[$b->c_category])) echo $c_category_arr[$b->c_category]; 
                                            else echo $c_category_arr_default[$b->c_category];                           
                                        }
                                        ?>
                                    </td>
                                @endif

                                @if(\vwmldbm\code\is_code_usable('c_category2'))
                                    <td class="category2-cell">
                                        <?PHP
                                        if($b->c_category2) {
                                            if(isset($c_category2_arr[$b->c_category2])) echo $c_category2_arr[$b->c_category2]; 
                                            else echo $c_category2_arr_default[$b->c_category2];                           
                                        }
                                        ?>
                                    </td>
                                @endif

                                    <td class="publisher-cell">                        
                                        <?
                                        if(isset($search_target) && $search_target=='i_publisher')
                                            echo \wlibrary\code\highlight($b->publisher,$search_word_arr);
                                        else if(!isset($search_target) && isset($search_word))
                                            echo \wlibrary\code\highlight($b->publisher,$search_word_arr);
                                        else echo $b->publisher;
                                        ?>
                                    </td>

                                    <td class="date-cell">
                                        @if($b->pub_date) 
                                            <span class="date-badge">{{$b->pub_date->format('Y-m-d')}}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="copies-cell">
                                        <?php
                                            $copy_num=$b->get_copy_num();
                                        ?>
                                        <span class="copies-badge">{{$copy_num}}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-container mt-4">
                    <div class="d-flex justify-content-center">
                        <?PHP                
                        if(isset($request)) {
                            echo $books->appends(request()->query())->links('vendor.pagination.bootstrap-4');                    
                        }
                        else echo $books->links('vendor.pagination.bootstrap-4');
                        ?>
                    </div>
                </div>
                
                {!!Form::close()!!}
            </div>
        </div>
    </div>
</div>

<!-- Cover Image Dialog -->
<div id="dialog" title="" style="display:none;">
    <img id='dialog_img' width='100%'>
</div>

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

    function open_cover_img(obj){ 
        $('#dialog').dialog('open');    
        $('#dialog_img').attr("src",obj.src);                                            
    }               
</script>

<style>
    .eshelf-container {
        background: #f8f9fa;
        min-height: 100vh;
    }

    .eshelf-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #2c3e50;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }

    .eshelf-icon {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        margin-right: 1.5rem;
    }

    .eshelf-icon img {
        filter: brightness(0) invert(1);
        width: 30px;
        height: 30px;
    }

    .eshelf-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
        line-height: 1.2;
    }

    .eshelf-subtitle {
        font-size: 1rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    .page-controls {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .page-size-selector {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .search-results-badge .badge {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }

    .books-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .eshelf-table {
        margin: 0;
    }

    .table-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #dee2e6;
    }

    .table-header th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .book-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f3f4;
    }

    .book-row:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .book-row td {
        padding: 1rem;
        vertical-align: middle;
        border: none;
    }

    .cover-cell {
        width: 80px;
        text-align: center;
    }

    .cover-image {
        height: 60px;
        width: auto;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .cover-image:hover {
        transform: scale(1.1);
    }

    .no-cover-placeholder {
        width: 60px;
        height: 60px;
        background: #e9ecef;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .title-cell {
        min-width: 200px;
    }

    .book-title-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .book-title-link:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .author-cell {
        min-width: 150px;
        color: #6c757d;
    }

    .type-cell {
        text-align: center;
    }

    .file-info {
        color: #28a745;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .date-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .copies-badge {
        background: #fff3e0;
        color: #f57c00;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .pagination-container {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .eshelf-header {
            padding: 1.5rem 0;
        }

        .eshelf-title {
            font-size: 1.75rem;
        }

        .eshelf-icon {
            width: 50px;
            height: 50px;
            margin-right: 1rem;
        }

        .eshelf-icon img {
            width: 25px;
            height: 25px;
        }

        .eshelf-subtitle {
            font-size: 0.9rem;
        }

        .page-controls {
            padding: 1rem;
        }

        .table-responsive {
            font-size: 0.9rem;
        }

        .book-row td {
            padding: 0.75rem 0.5rem;
        }

        .cover-image {
            height: 50px;
        }

        .no-cover-placeholder {
            width: 50px;
            height: 50px;
        }
    }
</style>
@endsection