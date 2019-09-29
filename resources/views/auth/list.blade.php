@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header">{{ __('Last Logins') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-{{ session('status.type') }}" role="alert">
                                {{ __(session('status.message')) }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">{{ __('auth_tracker.device_type') }}</th>
                                    <th scope="col">{{ __('auth_tracker.platform') }}</th>
                                    <th scope="col">{{ __('auth_tracker.browser') }}</th>
                                    <th scope="col">{{ __('auth_tracker.ip') }}</th>
                                    @ipLookup
                                        <th scope="col">{{ __('auth_tracker.location') }}</th>
                                    @endipLookup
                                    <th scope="col">{{ __('auth_tracker.last_login') }}</th>
                                    <th scope="col">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                data-toggle="modal" data-target="#confirmationModal">
                                            {{ __('auth_tracker.logout_all') }}
                                        </button>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($logins as $login)
                                    <tr>
                                        <td>{{ Illuminate\Support\Str::title($login->device_type) }}</td>
                                        <td>{{ $login->platform }}</td>
                                        <td>{{ $login->browser }}</td>
                                        <td>{{ $login->ip }}</td>
                                        @ipLookup
                                            <td>{{ $login->location }}</td>
                                        @endipLookup
                                        <td>
                                            @if ($login->is_current)
                                                <span class="badge badge-pill badge-primary">{{ __('auth_tracker.current') }}</span>
                                            @elseif ($login->updated_at->diffInDays() <= 7)
                                                {{ $login->updated_at->diffForHumans() }}
                                            @else
                                                {{ $login->updated_at }}
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('auth_tracker.logout', ['id' => $login->id]) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    {{ __('auth_tracker.logout') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Confirmation modal -->
                <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                {{ __('Are you sure you want to logout all the devices, including the current one?') }}
                                <br /><br />
                                {{ __('You can click the "Logout others" button to logout all the devices except the current one.') }}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('auth_tracker.cancel') }}</button>
                                <form action="{{ route('auth_tracker.logout.others') }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger">{{ __('auth_tracker.logout_others') }}</button>
                                </form>
                                <form action="{{ route('auth_tracker.logout.all') }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">{{ __('auth_tracker.logout_all') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
