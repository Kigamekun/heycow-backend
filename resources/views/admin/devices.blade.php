@extends('layouts.base')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css"
        integrity="sha512-EZSUkJWTjzDlspOoPSpUFR0o0Xy7jdzW//6qhUkoZ9c4StFkVsp9fbbd0O06p9ELS3H486m4wmrCELjza4JEog=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table> :not(:first-child) {
            border-top: none;
        }

        .dt-paging-button.active .page-link {
            color: white;
        }

        table.dataTable td {
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <br>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>IoT Devices</h3>
                    <div>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createDevice">
                            Add New Device
                        </button>
                    </div>
                </div>
                <br>
                <div class="table-responsive">
                    <table id="datatable-table" class="mt-3 mb-3 rounded-sm table border-none table-bordered table-md "
                        style="border-top: none">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th>Installation Date</th>
                                <th>QR Image</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createDevice" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="exampleModalLabel">Add New IoT Device</h5>
                        <small class="form-text text-muted">Fields marked with <span class="text-danger">*</span> are
                            required.</small>
                    </div>
                </div>
                <form action="{{ route('iotdevice.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="serial_number" class="fw-semibold">Serial Number <span
                                    class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control {{ $errors->has('serial_number') ? 'is-invalid' : '' }}"
                                id="serial_number" name="serial_number" placeholder="Enter Serial Number">
                            <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="status" class="fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="installation_date" class="fw-semibold">Installation Date <span
                                    class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control {{ $errors->has('installation_date') ? 'is-invalid' : '' }}"
                                id="installation_date" name="installation_date">
                            <x-input-error :messages="$errors->get('installation_date')" class="mt-2" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateDevice" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="updateDeviceLabel" aria-hidden="true">
        <div class="modal-dialog" id="updateDialog">
            <div id="modal-content" class="modal-content">
                <div class="modal-body">
                    Loading..
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.bootstrap4.js"></script>

    <script>
        $(function() {
            var table = $('#datatable-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('iotdevice.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'serial_number',
                        name: 'serial_number'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'installation_date',
                        name: 'installation_date'
                    },
                    {
                        data: 'qr_image',
                        name: 'qr_image'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'desc']
                ],
            });
        });

        $('#updateDevice').on('shown.bs.modal', function(e) {
            var serial_number = $(e.relatedTarget).data('serial_number');
            var status = $(e.relatedTarget).data('status');
            var installation_date = $(e.relatedTarget).data('installation_date');
            var html = `
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="staticBackdropLabel">Edit IoT Device</h5>
                            <small id="emailHelp" class="form-text text-muted">Field dengan tanda <span class="text-danger">*</span> wajib diisi.</small>
                        </div>
                    </div>
                    <form action="${$(e.relatedTarget).data('url')}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="serial_number" class="fw-semibold">Serial Number <span class="ml-1 text-danger">*</span></label>
                                <input type="text" class="form-control {{ $errors->has('serial_number') ? 'is-invalid' : '' }}" value="${serial_number}"
                                    id="serial_number" name="serial_number" placeholder="Masukan Serial Number">
                                <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                            </div>
                            <div class="mb-3">
                                <label for="status" class="fw-semibold">Status <span class="ml-1 text-danger">*</span></label>
                                <select name="status" id="status" class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                    <option value="active" ${status == 'active' ? 'selected' : ''}>Active</option>
                                    <option value="inactive" ${status == 'inactive' ? 'selected' : ''}>Inactive</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                            <div class="mb-3">
                                <label for="installation_date" class="fw-semibold">Installation Date <span class="ml-1 text-danger">*</span></label>
                                <input type="date" class="form-control {{ $errors->has('installation_date') ? 'is-invalid' : '' }}" value="${installation_date}"
                                    id="installation_date" name="installation_date" placeholder="Pilih Installation Date">
                                <x-input-error :messages="$errors->get('installation_date')" class="mt-2" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
            `;
            $('#modal-content').html(html);
            $('.dropify').dropify();

        });
    </script>
@endsection
