@extends('layouts.auth')

@section('content')
<h4 class="text-center mb-4">Verificar E-mail</h4>
                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success" role="alert">
                            {{ __('Um novo link de verificação foi enviado para o endereço de e-mail que você forneceu durante o registro.') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <p class="mb-0">
                            {{ __('Obrigado por se registrar! Antes de começar, você poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar para você? Se você não recebeu o e-mail, ficaremos felizes em enviar outro.') }}
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                {{ __('Reenviar E-mail de Verificação') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link text-decoration-none">
                                {{ __('Sair') }}
                            </button>
                        </form>
                    </div>

@endsection