@extends('layouts.root')

<?PHP
if(!isset($_SESSION['lib_inst'])) $_SESSION['lib_inst']=null;
$inst=$_SESSION['lib_inst'];

if(isset($_SESSION['inst_uname']))  $theInst=new vwmldbm\code\Inst_var(null,$_SESSION['inst_uname']);
else $theInst=new vwmldbm\code\Inst_var($inst,null);

if($inst==config('app.inst')) $inst_mode=config('app.mode',''); // Default Institution
else $inst_mode=$theInst->mode;

if($inst_mode=='WITH_ERP') {	
    if($theInst->no==config('app.inst')) { // default inst
        $inst_id=config('app.inst_id');
        $inst_uname=config('app.inst_uname');
        $inst_secret=config('app.inst_secret');
    }
    else { // other inst rather than default one
        $inst_id=$theInst->inst_id;
        $inst_uname=$theInst->inst_uname;
        $inst_secret=$theInst->secret;
    }

    if(config('app.wv2_login_uri','')=='') die("Check your login URI Setting!"); // to prevent infinite self submission
    
    echo "
        <form name='wv2_login' action='".config('app.wv2_login_uri','')."' method='POST'>
            <input type='hidden' name='inst_id' value='{$inst_id}'>
            <input type='hidden' name='inst_uname' value='{$inst_uname}'>
            <input type='hidden' name='inst_secret' value='{$inst_secret}'>
            <script>
                window.onload = function(){
                    document.wv2_login.submit();
                }  
            </script>    
        </form>
    ";
    die;
}

?>

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        @if(false)
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (false && Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <input type='hidden' name='inst' value='<?=$inst?>'>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection