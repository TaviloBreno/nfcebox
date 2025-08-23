@extends('layouts.auth')

@section('content')
<h4 class="text-center mb-4">Redefinir Senha</h4>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('E-mail') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Nova Senha') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="new-password">
                            
                            <div class="form-text">
                                <small class="text-muted">
                                    A senha deve conter pelo menos:
                                    <ul class="mb-0 mt-1">
                                        <li id="length" class="text-danger">8 caracteres</li>
                                        <li id="lowercase" class="text-danger">1 letra minúscula</li>
                                        <li id="uppercase" class="text-danger">1 letra maiúscula</li>
                                        <li id="number" class="text-danger">1 número</li>
                                        <li id="special" class="text-danger">1 caractere especial (@$!%*?&)</li>
                                    </ul>
                                </small>
                            </div>

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">{{ __('Confirmar Nova Senha') }}</label>
                            <input id="password-confirm" type="password" class="form-control" 
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-info">
                                {{ __('Redefinir Senha') }}
                            </button>
                        </div>
                    </form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const lengthCheck = document.getElementById('length');
    const lowercaseCheck = document.getElementById('lowercase');
    const uppercaseCheck = document.getElementById('uppercase');
    const numberCheck = document.getElementById('number');
    const specialCheck = document.getElementById('special');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        // Verificar comprimento
        if (password.length >= 8) {
            lengthCheck.className = 'text-success';
        } else {
            lengthCheck.className = 'text-danger';
        }
        
        // Verificar letra minúscula
        if (/[a-z]/.test(password)) {
            lowercaseCheck.className = 'text-success';
        } else {
            lowercaseCheck.className = 'text-danger';
        }
        
        // Verificar letra maiúscula
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.className = 'text-success';
        } else {
            uppercaseCheck.className = 'text-danger';
        }
        
        // Verificar número
        if (/\d/.test(password)) {
            numberCheck.className = 'text-success';
        } else {
            numberCheck.className = 'text-danger';
        }
        
        // Verificar caractere especial
        if (/[@$!%*?&]/.test(password)) {
            specialCheck.className = 'text-success';
        } else {
            specialCheck.className = 'text-danger';
        }
    });
});
</script>

@endsection