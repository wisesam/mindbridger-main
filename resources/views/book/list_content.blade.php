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
    if(isset($operation) && $operation=='ASEARCH') { // Advanced Search  
        if($request->title!==null)
            $search_word_arr[]=$request->title;
        if($request->author!==null)
            $search_word_arr[]=$request->author;
        if($request->publisher!==null)
            $search_word_arr[]=$request->publisher;
        if($request->isbn!==null)
            $search_word_arr[]=$request->isbn;
    }
    else if(isset($search_word)) { // search from the nav bar
        $search_word_arr[]=$search_word;
    }
    else $operation=null;
?>

<div class="resource-list-container">
    <!-- Header Section -->
    <div class="resource-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="resource-icon me-3">
                            <img src="image/book.png?nocache=4" alt="Resources" width="40" height="40">
                        </div>
                        <div>
                            <h1 class="resource-title mb-1">{{__("Resource List")}}</h1>
                            <p class="resource-subtitle text-muted mb-0">{{__("Browse our complete collection")}}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    @if(Auth::check() && Auth::user()->isAdmin() && !isset($no_add_btt)) 
                    <button type='button' class='btn btn-primary btn-lg' onClick="window.location='{{config('app.url','/wlibrary')}}/book/create'">
                        <i class="fas fa-plus me-2"></i>{{__("Add Resource")}}
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                {!!Form::open(['method'=>'GET','class'=>'resource-form','name'=>'bookListForm'])!!}
                
                <!-- Page Controls -->
                <div class="page-controls mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
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
                            <span class="badge bg-info text-white fs-6">{{__('Search Results')}}: {{$bnum}}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Resources Table -->
                <div class="resources-table-container">
                    <div class="table-responsive">
                        <table class="table table-hover resource-table">
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
                                <tr class="resource-row">
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
                                        <a href="{{config('app.url','/wlibrary')}}/book/{{$b->id}}" class="resource-link">
                                            <?
                                            if(isset($search_target) && $search_target=='i_title')
                                                echo \wlibrary\code\highlight($b->title,$search_word_arr);
                                            else if(!isset($search_target) && isset($search_word))
                                                echo \wlibrary\code\highlight($b->title,$search_word_arr);
                                            else if($request->title!==null)
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
                                        else if($request->author!==null)
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
                                            if(isset($c_grade_arr[$b->c_grade])) echo $c_grade_arr[$b->c_grade]; 
                                            else echo $c_grade_arr_default[$b->c_grade];                           
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
                                        else if($request->publisher!==null)
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

                                    @if(Auth::check() && Auth::user()->isAdmin()) 
                                    <td class="action-cell">
                                        <a href="{{config('app.url','/wlibrary')}}/book/{{$b->id}}/edit" class="action-link">
                                            <i class="fas fa-edit text-primary"></i>
                                        </a>
                                    </td>
                                    <td class="action-cell">
                                        @if(!$copy_num)
                                        <a href="javascript:confirm_delete('{{$b->title}}','{{$b->id}}');" class="action-link">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>                        
                                        @endif
                                    </td>
                                    @endif
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
                        else  echo $books->links('vendor.pagination.bootstrap-4');
                        ?>
                    </div>
                </div>
                {!!Form::close()!!}
                
                <script>
                    function confirm_delete(title,id) {
                        if(confirm("Are you sure you want to delete \""+title+"\" ?")) {
                            document.getElementById('bcDelForm').action="{{config('app.url','/wlibrary')}}/book/"+id;
                            document.getElementById('bcDelForm').submit();
                        }
                    }
                </script>
            </div>
            <form  id='bcDelForm' method='POST'>
                @csrf
                @method('delete')
            </form>
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
    .resource-list-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding-bottom: 2rem;
    }

    .resource-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #2c3e50;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }

    .resource-icon {
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

    .resource-icon img {
        filter: brightness(0) invert(1);
        width: 30px;
        height: 30px;
    }

    .resource-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
        line-height: 1.2;
    }

    .resource-subtitle {
        font-size: 1rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    .page-controls {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .search-results-badge .badge {
        font-size: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
    }

    .resources-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .resource-table {
        margin: 0;
        border: none;
    }

    .table-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }

    .table-header th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        text-align: left;
        vertical-align: middle;
    }

    .resource-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f3f4;
    }

    .resource-row:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .resource-row td {
        padding: 1rem;
        vertical-align: middle;
        border: none;
    }

    .cover-cell {
        width: 120px;
        text-align: center;
    }

    .cover-image {
        height: 80px;
        width: auto;
        max-width: 100px;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .cover-image:hover {
        transform: scale(1.05);
    }

    .no-cover-placeholder {
        width: 80px;
        height: 80px;
        background: #e9ecef;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .title-cell { min-width: 200px; }
    .author-cell { min-width: 150px; }
    .type-cell { text-align: center; min-width: 100px; }
    .rtype-cell { min-width: 120px; }
    .genre-cell, .grade-cell, .category-cell, .category2-cell { min-width: 100px; }
    .publisher-cell { min-width: 150px; }
    .date-cell { min-width: 120px; }
    .copies-cell { min-width: 80px; text-align: center; }
    .action-cell { min-width: 60px; text-align: center; }

    .resource-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .resource-link:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .file-info {
        color: #28a745;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .date-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .copies-badge {
        background: #f3e5f5;
        color: #7b1fa2;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .action-link {
        color: #6c757d;
        transition: all 0.3s ease;
        padding: 0.5rem;
        border-radius: 6px;
        display: inline-block;
    }

    .action-link:hover {
        background: #f8f9fa;
        transform: scale(1.1);
    }

    .pagination-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .resource-header {
            padding: 1.5rem 0;
        }

        .resource-title {
            font-size: 1.75rem;
        }

        .resource-icon {
            width: 50px;
            height: 50px;
            margin-right: 1rem;
        }

        .resource-icon img {
            width: 25px;
            height: 25px;
        }

        .resource-subtitle {
            font-size: 0.9rem;
        }

        .page-controls {
            padding: 1rem;
        }

        .table-responsive {
            font-size: 0.9rem;
        }

        .resource-row td {
            padding: 0.75rem 0.5rem;
        }

        .cover-image {
            height: 60px;
        }

        .no-cover-placeholder {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
    }
</style>