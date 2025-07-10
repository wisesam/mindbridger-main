<?PHP
// Pre-loading the field values for performance
$field_arr=array();
\vwmldbm\code\get_field_name_all('rental',$field_arr);

$field_arr_book=array();
\vwmldbm\code\get_field_name_all('book',$field_arr_book);

// Pre-loading the code values for performance
$c_rent_status_arr=array();
\vwmldbm\code\get_code_name_all($c_rent_status_arr,'code_c_rent_status');

$c_rent_status_arr_default=array();
\vwmldbm\code\get_code_name_all($c_rent_status_arr_default,'code_c_rent_status',null,10);

if(Auth::user()->isAdmin()) $admin_mode=true;
else $admin_mode=false;
// $rnum=$rentals->total();

$rental_terminated_yn_arr=array();
\vwmldbm\code\get_code_name_all($rental_terminated_yn_arr,'code_c_rent_status','rental_terminated_yn');

?>

<div class="card-body">
    {!!Form::open(['method'=>'POST','class'=>'float-center','id'=>'bDelForm'])!!}
    @if(isset($new_rental_ok) && $new_rental_ok)
        <?php
            if($rentals[0]) {
                $book_copy = App\Book_copy::where('inst',$_SESSION['lib_inst'])
                    ->where('id',$rentals[0]->bcid)
                    ->first();
            }
            else { // no rental records
                // $book_copy was passed from the conroller already

            }
            if(\vwmldbm\code\get_c_name('code_c_rstatus',$book_copy->c_rstatus,'available_yn')=='Y') $available_yn=true;
            else $available_yn=false;
        ?>
        <p>
        @if($available_yn && Auth::user()->isAdmin())        
            <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')."/rental/create/".$book_copy->id}}'">
                {{ __('Add new rental') }}
            </button>
        
        @elseif(Auth::user()->isAdmin())
            <h4 style='color:pink;'>{{__("New rental not available!")}}</h4>
        @endif
        </p>
    @endif
    <div>

    </div>
    <div class="table-responsive">
        <script>
            $(document).ready(function (){
                $( "#dialog" ).dialog({
                    width:'75%',
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
        <div id="dialog" title="" style="display:none; align-top;">
            <img id='dialog_img' width='100%'>
        </div> 

    <table class="table table-striped">
        <tr>
            <th> </th>
            <th>{{$field_arr["bcid"]}}</th>
            <th>{{$field_arr_book["title"]}}</th>
            <th>{{$field_arr["uid"]}}</th>            
            <th>{{$field_arr["rent_date"]}}</th>
            <th>{{$field_arr["due_date"]}}</th>
            <th>{{$field_arr["return_date"]}}</th>
            <th>{{$field_arr["c_rent_status"]}}</th>
            <th>{{$field_arr["rcomment"]}}</th>
            
            @if(Auth::check() && Auth::user()->isAdmin()) 
            <th></th>
            <th></th>
            @endif
        </tr>                
        @if(isset($rentals) && count($rentals))
            @foreach($rentals as $r)
            
            <?PHP
                $book_copy = App\Book_copy::where('inst',$_SESSION['lib_inst'])
                    ->where('id',$r->bcid)
                    ->first();
                
                $book = App\Book::where('inst',$_SESSION['lib_inst'])
                    ->where('id',$book_copy->bid)
                    ->first();

                $late_rental_exist=$r->isOverdue($rental_terminated_yn_arr);
                $over_due_tag=null;
                if($late_rental_exist>0) $over_due_tag="<font color='red'> (".__("Overdue").")</font>";
                
            ?>
            
            <tr>
                <td></td>
                <td>{{$r->bcid}}</td>  
                <td>{{$book->title}}</td>  
                <td>{{$r->uid}}</td>  
                <td>{{$r->rent_date->format('Y-m-d H:i')}}</td>  
                <td>{{$r->due_date->format('Y-m-d H:i')}}</td>  

                <td>
                    <?PHP
                        if($r->return_date) echo $r->return_date->format('Y-m-d H:i')
                    ?>
                </td>  
                
                <td>
                    <?PHP
                    if($r->c_rent_status) {                        
                        $scolor=App\Rental::print_status_color($r->c_rent_status,$rental_terminated_yn_arr);
                        if($admin_mode) {
                            $aTag= "<a href='".config('app.url','/wlibrary')."/rental/".$r->id."/edit'>";
                            if(isset($c_rent_status_arr[$r->c_rent_status])) echo $aTag."<font color='$scolor'>".$c_rent_status_arr[$r->c_rent_status]."</font></a>"; 
                            else echo $aTag."<font color='$scolor'>".$c_rent_status_arr_default[$r->c_rent_status]."</a>";    
                        }
                        else {                            
                            if(isset($c_rent_status_arr[$r->c_rent_status])) echo "<font color='$scolor'>".$c_rent_status_arr[$r->c_rent_status]; 
                            else echo "<font color='$scolor'>".$c_rent_status_arr_default[$r->c_rent_status];    
                        }                        
                        echo $over_due_tag;
                    }
                    ?>
                </td>

                <td>
                    <?php 
                        if(mb_strlen($r->rcomment) > 20) {
                            $rcomment=mb_substr($r->rcomment,0,18)."..";
                        }
                        else $rcomment=$r->rcomment;
                    ?>    
                    {{$rcomment}} 
                </td> 
                @if(Auth::check() && Auth::user()->isAdmin()) 
                <td></td>
                <td></td>
                @endif
            </tr>
            @endforeach
        @endif
    </table>
    
    <div class="d-flex">
        <div class="mx-auto">
            <?PHP 
            if(isset($rentals[0])) {
                if(isset($request)) {
                    echo $rentals->appends(request()->query())->links('vendor.pagination.bootstrap-4');                    
                }
                else  echo $rentals->links('vendor.pagination.bootstrap-4');
            }        
            ?>
        </div>
    </div>

    {{Form::hidden('_method','DELETE')}}
    {!!Form::close()!!}
    
    <script>
        function confirm_delete(title,id) {
            if(confirm("Are you sure you want to delete \""+title+"\" ?")) {
                document.getElementById('bDelForm').action="{{config('app.url','/wlibrary')}}/book/"+id;
                document.getElementById('bDelForm').submit();
            }
        }
    </script>
</div>