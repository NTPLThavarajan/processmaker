@extends('layouts.layout')

@section('title')
    {{__('Users')}}
@endsection

@section('sidebar')
    @include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_admin')])
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumbs', ['routes' => [
        __('Admin') => route('admin.index'),
        __('Users') => null,
    ]])
@endsection
@section('content')
    <div class="px-3 page-content" id="users-listing">
        <div class="row">
            <div class="col">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input v-model="filter" class="form-control" placeholder="{{__('Search')}}...">
                </div>

            </div>
            <div class="col-8" align="right">
                @can('create-users')
                    <button type="button" class="btn btn-action text-light" data-toggle="modal" data-target="#addUser"
                            id="addUserBtn">
                        <i class="fas fa-plus"></i>
                        {{__('User')}}</button>
                @endcan
            </div>
        </div>
        <div class="container-fluid">
            <users-listing ref="listing" :filter="filter" :permission="{{ \Auth::user()->hasPermissionsFor('users') }}"
                           v-on:reload="reload"></users-listing>
        </div>
    </div>

    @can('create-users')
        <div class="modal" tabindex="-10" role="dialog" id="addUser">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Create User')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            {!!Form::label('username', __('Username'))!!}<small class="ml-1">*</small>
                            {!!Form::text('username', null, ['class'=> 'form-control', 'v-model'=> 'username', 'v-bind:class'
                            => '{\'form-control\':true, \'is-invalid\':addError.username}', 'autocomplete' => 'off']) !!}
                            <div class="invalid-feedback" v-for="username in addError.username">
                                <div v-if="username !== 'userExists'"> 
                                    @{{username}} 
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!!Form::label('firstname', __('First Name'))!!}<small class="ml-1">*</small>
                            {!!Form::text('firstname', null, ['class'=> 'form-control', 'v-model'=> 'firstname', 'v-bind:class'
                            => '{\'form-control\':true, \'is-invalid\':addError.firstname}'])!!}
                            <div class="invalid-feedback" v-for="firstname in addError.firstname">@{{firstname}}</div>
                        </div>
                        <div class="form-group">
                            {!!Form::label('lastname', __('Last Name'))!!}<small class="ml-1">*</small>
                            {!!Form::text('lastname', null, ['class'=> 'form-control', 'v-model'=> 'lastname', 'v-bind:class'
                            => '{\'form-control\':true, \'is-invalid\':addError.lastname}'])!!}
                            <div class="invalid-feedback" v-for="lastname in addError.lastname">@{{lastname}}</div>
                        </div>
                        <div class="form-group">
                            {!!Form::label('title', __('Job Title'))!!}
                            {!!Form::text('title', null, ['class'=> 'form-control', 'v-model'=> 'title', 'v-bind:class'
                            => '{\'form-control\':true, \'is-invalid\':addError.title}'])!!}
                            <div class="invalid-feedback" v-for="title in addError.title">@{{title}}</div>
                        </div>
                        <div class="form-group">
                            {!!Form::label('status', __('Status'));!!}<small class="ml-1">*</small>
                            {!!Form::select('size',[null => __('Select')]+['ACTIVE' => __('Active'), 'INACTIVE' => __('Inactive')], 'Active',
                            [
                            'class'=> 'form-control', 'v-model'=> 'status',
                            'v-bind:class' => '{\'form-control\':true, \'is-invalid\':addError.status}']);!!}
                            <div class="invalid-feedback" v-for="status in addError.status">@{{status}}</div>
                        </div>
                        <div class="form-group">
                            {!!Form::label('email', __('Email'))!!}<small class="ml-1">*</small>
                            {!!Form::email('email', null, ['class'=> 'form-control', 'v-model'=> 'email', 'v-bind:class' =>
                            '{\'form-control\':true, \'is-invalid\':addError.email}', 'autocomplete' => 'off'])!!}
                            <div class="invalid-feedback" v-for="email in addError.email">
                                <div v-if="email !== 'userExists'">
                                    @{{email}}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!!Form::label('password', __('Password'))!!}<small class="ml-1">*</small>
                            <vue-password v-model="password" :disable-toggle=true ref="passwordStrength">
                                <div slot="password-input" slot-scope="props">
                                    {!!Form::password('password', ['class'=> 'form-control', 'v-model'=> 'password',
                                    '@input' => 'props.updatePassword($event.target.value)', 'autocomplete' => 'new-password',
                                    'v-bind:class' => '{\'form-control\':true, \'is-invalid\':addError.password}'])!!}
                                </div>
                            </vue-password>
                        </div>
                        <div class="form-group">
                            {!!Form::label('confpassword', __('Confirm Password'))!!}<small class="ml-1">*</small>
                            {!!Form::password('confpassword', ['class'=> 'form-control', 'v-model'=> 'confpassword',
                            'v-bind:class' => '{\'form-control\':true, \'is-invalid\':addError.password}', 'autocomplete' => 'new-password'])!!}
                            <div class="invalid-feedback" v-for="password in addError.password">@{{password}}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            {{__('Cancel')}}
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" @click="onSubmit" :disabled="disabled">
                            {{__('Save')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" tabindex="-10" role="dialog" id="restoreUser">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Deleted User Found')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('An existing user has been found with the ')}} @{{ restoreAttribute + ' "' + restoreValue + '"' }} {{__(' would you like to save and reactivate their account?') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" @click="onCancelRestore">
                            {{__('Cancel')}}
                        </button>
                        <button class="btn btn-secondary ml-2" @click="onSaveRestore">
                            {{__('Save')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@section('js')
    <script src="{{mix('js/admin/users/index.js')}}"></script>

    @can('create-users')
        <script>
        var addUserModal = new Vue({
            el: '#addUser',
            data: {
              username: '',
              firstname: '',
              lastname: '',
              title: '',
              status: '',
              email: '',
              password: '',
              confpassword: '',
              addError: {},
              submitted: false,
              disabled: false
            },
            methods: {
              onClose() {
                this.username = '';
                this.firstname = '';
                this.lastname = '';
                this.status = '';
                this.title = '',
                this.email = '';
                this.password = '';
                this.confpassword = '';
                this.addError = {};
                this.submitted = false;
              },
              validatePassword() {
                if (this.password.trim().length > 0 && this.password.trim().length < 8) {
                  this.addError.password = ['Password must be at least 8 characters']
                  this.$refs.passwordStrength.updatePassword('')
                  this.password = ''
                  this.confpassword = ''
                  this.submitted = false
                  return false
                }
                if (this.password !== this.confpassword) {
                  this.addError.password = ['Passwords must match']
                  this.$refs.passwordStrength.updatePassword('')
                  this.password = ''
                  this.confpassword = ''
                  this.submitted = false
                  return false
                }
                return true
              },
              onSubmit() {
                this.submitted = true;
                if (this.validatePassword()) {
                  //single click
                  if (this.disabled) {
                    return
                  }
                  this.disabled = true;
                  ProcessMaker.apiClient.post("/users", {
                    username: this.username,
                    firstname: this.firstname,
                    lastname: this.lastname,
                    title: this.title,
                    status: this.status,
                    email: this.email,
                    password: this.password
                  }).then(function (response) {
                    window.location = "/admin/users/" + response.data.id + '/edit?created=true'
                  }).catch(error => {
                    this.addError = error.response.data.errors;
                    this.disabled = false;
                    if (!Object.values(this.addError).some(field => field.includes('userExists'))) {
                        if (this.addError.email) {
                            restoreUserModal.emailToRestore = restoreUserModal.restoreValue = this.email;
                            restoreUserModal.restoreAttribute = 'email address';
                        } else if (this.addError.username) {
                            restoreUserModal.usernameToRestore = restoreUserModal.restoreValue= this.username;
                            restoreUserModal.restoreAttribute = 'username';
                        }
                        $('#addUser').modal('hide');
                        $('#restoreUser').modal('show');
                    }
                  });
                }
              }
            },
            mounted() {
              $('#addUser').on('hidden.bs.modal', () => {
                this.onClose();
              });
            }
        });

        var restoreUserModal = new Vue({
            el: '#restoreUser',
            data(){
                return {
                    restoreValue: '',
                    restoreAttribute: '',
                    emailToRestore: '',
                    usernameToRestore: ''
                }
            },
            methods: {
                onCancelRestore() {
                    $('#restoreUser').modal('hide');
                },
                onSaveRestore() {
                    let data = [];
                    if (this.emailToRestore !== '') {
                        this.data = {
                            email: this.emailToRestore
                        };
                    }

                    if (this.usernameToRestore !== '') {
                        this.data = {
                            username: this.usernameToRestore
                        };
                    }
                    ProcessMaker.apiClient.put('/users/restore', this.data)
                    .then(response => {
                        $('#restoreUser').modal('hide');
                        ProcessMaker.alert(this.$t("The user was restored."), "success");
                        location.reload();
                    })
                    .catch(error => {
                        ProcessMaker.alert(error, "danger");
                    });
                }
            },
        });
        </script>
    @endcan
@endsection
@section('css')
    <style>
        /* .multiselect__tag {
              background: #788793 !important;
            } */
        .multiselect__element span img {
            border-radius: 50%;
            height: 20px;
        }

        .multiselect__tags-wrap {
            display: flex !important;
        }

        .multiselect__tags-wrap img {
            height: 15px;
            border-radius: 50%;
        }

        .multiselect__tag-icon:after {
            color: white !important;
        }

        /* .multiselect__tag-icon:focus, .multiselect__tag-icon:hover {
               background: #788793 !important;
            } */
        .multiselect__option--highlight {
            background: #00bf9c !important;
        }

        .multiselect__option--selected.multiselect__option--highlight {
            background: #00bf9c !important;
        }

        .multiselect__tags {
            border: 1px solid #b6bfc6 !important;
            border-radius: 0.125em !important;
            height: calc(1.875rem + 2px) !important;
        }

        .multiselect__tag {
            background: #788793 !important;
        }

        .multiselect__tag-icon:after {
            color: white !important;
        }
    </style>
@endsection
