@extends('adminmodule::layouts.master')

@section('title', translate('Employee_Attributes'))

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between mb-3">
                <h2 class="fs-22">{{translate('Employee_Attributes')}}</h2>
            </div>

            <div class="card">
                <form id="form_data" action="{{route('admin.employee.role.update', ['id' => $role['id']])}}"
                      method="post">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <h6 class="fw-semibold mb-4">{{translate('update_role')}}</h6>

                        <div class="bg-fafafa p-4 rounded mb-4">
                            <label for="role-name" class="mb-2">
                                {{translate('role_name')}}</label>
                            <input type="text" name="name" value="{{$role['name']}}" class="form-control"
                                   placeholder="{{translate('Ex: Business Analyst')}}" tabindex="1">
                        </div>

                        <div class="d-flex flex-wrap align-items-center column-gap-4 row-gap-2">
                            <h6 class="fw-medium mt-5 mb-3 text-capitalize">{{translate('available_modules')}}</h6>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" id="select-all-modules" {{ count($role->modules ?? []) == count(MODULES) ? 'checked' : '' }}>
                                        {{ translate('Select_All') }}
                                    </label>
                                </div>
                                @foreach(MODULES as $key => $module)
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                        <label class="custom-checkbox">
                                            <input type="checkbox" name="modules[]"
                                                class="module-checkbox"   value="{{ $key }}" {{ in_array($key, $role->modules) ? 'checked' : '' }}>
                                            {{ translate($key) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4" id="module-permissions-grid">
                            <h6 class="fw-bold mb-3 text-capitalize">{{translate('Action Permissions per Module')}}</h6>
                            <p class="text-muted small mb-3">{{translate('Select which actions each module allows. These will be used as defaults when assigning employees.')}}</p>
                            @php
                                $savedPermissions = [];
                                if (isset($role->permissions) && is_array($role->permissions)) {
                                    $savedPermissions = $role->permissions;
                                }
                            @endphp
                            @foreach(MODULES as $key => $actions)
                                <div class="card border-0 shadow-none mb-2 module-permission-block" data-module="{{$key}}" style="display:none;">
                                    <div class="p-3 pb-0">
                                        <div class="d-flex gap-3 flex-wrap justify-content-between align-items-center">
                                            <h6 class="fw-semibold text-capitalize">{{translate($key)}}</h6>
                                            <label class="custom-checkbox">
                                                <input type="checkbox" class="select-all-actions" data-module="{{$key}}">
                                                {{translate('Select all')}}
                                            </label>
                                        </div>
                                        <hr class="off-white-gray">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row">
                                            @foreach($actions as $permission)
                                                <div class="col">
                                                    <label class="custom-checkbox pb-3">
                                                        <input type="checkbox" name="permissions[{{$key}}][]" value="{{$permission}}" class="action-checkbox" data-module="{{$key}}"
                                                            {{ (isset($savedPermissions[$key]) && in_array($permission, $savedPermissions[$key])) ? 'checked' : '' }}>
                                                        {{translate($permission)}}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex gap-3 flex-wrap justify-content-end mt-5">
                            <button
                                class="btn btn-secondary h-40px min-w-100px justify-content-center text-uppercase" tabindex="2"
                                type="reset">{{ translate('Reset') }}</button>
                            <button type="submit" tabindex="3"
                                    class="btn btn-primary h-40px min-w-100px justify-content-center text-uppercase">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection

@push('script')
<script>
    document.getElementById('select-all-modules').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.module-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = this.checked;
        }, this);
        updatePermissionBlocks();
        updateSelectAllStatus();
    });

    document.querySelectorAll('.module-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updatePermissionBlocks();
            updateSelectAllStatus();
        });
    });

    function updatePermissionBlocks() {
        document.querySelectorAll('.module-checkbox').forEach(function (cb) {
            var block = document.querySelector('.module-permission-block[data-module="' + cb.value + '"]');
            if (block) {
                block.style.display = cb.checked ? '' : 'none';
                if (!cb.checked) {
                    block.querySelectorAll('.action-checkbox').forEach(function (ac) { ac.checked = false; });
                }
            }
        });
    }

    document.querySelectorAll('.select-all-actions').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var mod = this.dataset.module;
            document.querySelectorAll('.action-checkbox[data-module="' + mod + '"]').forEach(function (ac) {
                ac.checked = this.checked;
            }, this);
        });
    });

    function updateSelectAllStatus() {
        var checkboxes = document.querySelectorAll('.module-checkbox');
        var selectAllCheckbox = document.getElementById('select-all-modules');

        var allChecked = true;
        var anyChecked = false;

        checkboxes.forEach(function(checkbox) {
            if (!checkbox.checked) {
                allChecked = false;
            } else {
                anyChecked = true;
            }
        });

        if (anyChecked) {
            selectAllCheckbox.checked = allChecked;
        } else {
            selectAllCheckbox.checked = false;
        }
    }

    updatePermissionBlocks();
</script>
@endpush
