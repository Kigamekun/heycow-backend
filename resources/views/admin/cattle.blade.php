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
    @php
        $breadcrumbs = [
            ['title' => 'Home', 'link' => '/', 'active' => false],
            ['title' => 'Cattle', 'link' => '/cattle', 'active' => true],
        ];
    @endphp

    <div class="container-fluid">
        <br>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>Cattle</h3>
                    <div>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createCattle">
                            Add New Cattle
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
                                <th>Name</th>
                                <th>Breed</th>
                                <th>Status</th>
                                <th>Gender</th>
                                <th>Birth Date</th>
                                <th>Birth Weight</th>
                                <th>Birth Height</th>
                                <th>Farm</th>
                                <th>IoT Device ID</th>
                                <th>Image</th>
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

    <!-- Modal to Add Cattle -->
    <div class="modal fade" id="createCattle" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="exampleModalLabel">Add New Cattle</h5>
                        <small class="form-text text-muted">Fields marked with <span class="text-danger">*</span> are
                            required.</small>
                    </div>
                </div>
                <form action="{{ route('cattle.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                id="name" name="name" placeholder="Enter Cattle Name">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="breed" class="fw-semibold">Breed <span class="text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('breed') ? 'is-invalid' : '' }}"
                                id="breed" name="breed" placeholder="Enter Cattle Breed">
                            <x-input-error :messages="$errors->get('breed')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="status" class="fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <option value="">Select Status</option>
                                <option value="alive">Alive</option>
                                <option value="dead">Dead</option>
                                <option value="sold">Sold</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="status"
                                class="form-control {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="birth_date" class="fw-semibold">Birth Date <span
                                    class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control {{ $errors->has('birth_date') ? 'is-invalid' : '' }}"
                                id="birth_date" name="birth_date">
                            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="birth_weight" class="fw-semibold">Birth Weight (kg) <span
                                    class="text-danger">*</span></label>
                            <input type="number"
                                class="form-control {{ $errors->has('birth_weight') ? 'is-invalid' : '' }}"
                                id="birth_weight" name="birth_weight" placeholder="Enter Birth Weight">
                            <x-input-error :messages="$errors->get('birth_weight')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="birth_height" class="fw-semibold">Birth Height (cm) <span
                                    class="text-danger">*</span></label>
                            <input type="number"
                                class="form-control {{ $errors->has('birth_weight') ? 'is-invalid' : '' }}"
                                id="birth_height" name="birth_height" placeholder="Enter Birth Height">
                            <x-input-error :messages="$errors->get('birth_height')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="farm_id" class="fw-semibold">Farm <span class="text-danger">*</span></label>
                            <select name="farm_id" id="farm_id"
                                class="form-control {{ $errors->has('farm_id') ? 'is-invalid' : '' }}">
                                <!-- Populate farms from the database -->
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('farm_id')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="iot_device_id" class="fw-semibold">IoT Device <span class="text-danger">*</span></label>
                            <select name="iot_device_id" id="iot_device_id"
                                class="form-control {{ $errors->has('iot_device_id') ? 'is-invalid' : '' }}">
                                <option value="">Select IoT Device</option>
                                @foreach ($iot_devices as $iotdevice)
                                    <option value="{{ $iotdevice->id }}">{{ $iotdevice->serial_number }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('iot_devices_id')" class="mt-2" />
                        </div>


                        <div class="mb-3">
                            <label for="image" class="fw-semibold">Image</label>
                            <input type="file" class="form-control"
                                id="image" name="image">
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
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

    <!-- Modal to Update Cattle -->
    <div class="modal fade" id="updateCattle" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="updateCattleLabel" aria-hidden="true">
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
    @if(Auth::user()->role !== 'admin')
    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
    @else
        <select name="user_id" class="form-control">
            @foreach($owners as $owner)
                <option value="{{ $owner->id }}">{{ $owner->name }}</option>
            @endforeach
        </select>
    @endif

    <script>
        $(function() {
            var table = $('#datatable-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('cattle.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'breed',
                        name: 'breed'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'gender',
                        name: 'gender'
                    },
                    {
                        data: 'birth_date',
                        name: 'birth_date'
                    },
                    {
                        data: 'birth_weight',
                        name: 'birth_weight'
                    },
                    {
                        data: 'birth_height',
                        name: 'birth_height'
                    },
                    {
                        data: 'farm_name',
                        name: 'farm_name'
                    },
                    {
                        data: 'iot',
                        name: 'iot'
                    },
                    {
                        data: 'image',
                        name: 'image'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });

        $('#updateCattle').on('shown.bs.modal', function(e) {
            // Dynamically get owner options
            var ownerOptions = '';
            var selectedIoTDeviceId = $(e.relatedTarget).data('iot_device_id');


            var owners = @json($owners); // Assuming you passed the list of owners as $owners
            var selectedOwner = $(e.relatedTarget).data('user_id');
            owners.forEach(function(owner) {
                var isSelected = owner.id == selectedOwner ? 'selected' : '';
                ownerOptions += `<option value="${owner.id}" ${isSelected}>${owner.name}</option>`;
            });

            var iotDeviceOptions = '';
            var iotDevices = @json($iot_devices);  // Assuming you're passing the devices in this variable
            iotDevices.forEach(function(device) {
                var isSelected = device.id == selectedIoTDeviceId ? 'selected' : '';
                iotDeviceOptions += `<option value="${device.id}" ${isSelected}>${device.serial_number}</option>`;
            });
            var farmId = $(e.relatedTarget).data('farm_id'); // Get the farm ID
            var farmName = ''; // Variable to hold the farm name

            // Assuming you have a JavaScript array of farms available
            var farms = @json($farms); // This should be an array of farm objects with id and name

            // Find the farm name using the farm ID
            farms.forEach(function(farm) {
                var isSelected = farm.id == farmId ? 'selected' : '';
                farmName += `<option value="${farm.id}" ${isSelected}>${farm.name}</option>`;
                // if (farm.id == farmId) {
                //     farmName = farm.name; // Get the farm name
            });



            var html = `
        <div class="modal-header">
            <div>
                <h5 class="modal-title" id="staticBackdropLabel">Edit Cattle</h5>
                <small id="emailHelp" class="form-text text-muted">Field dengan tanda <span class="text-danger">*</span> wajib diisi.</small>
            </div>
        </div>
        <form action="${$(e.relatedTarget).data('url')}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="fw-semibold">Nama<span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="${$(e.relatedTarget).data('name')}"
                                placeholder="Masukan Nama" required>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="breed" class="fw-semibold">Email<span class="ml-1 text-danger">*</span></label>
                            <input type="breed" class="form-control" id="breed" name="breed" value="${$(e.relatedTarget).data('breed')}"
                                placeholder="Masukan Breed" required>
                            <x-input-error :messages="$errors->get('breed')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="status" class="fw-semibold">Role<span class="ml-1 text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">Pilih Status</option>
                                <option value="alive" ${$(e.relatedTarget).data('status') == 'alive' ? 'selected' : ''}>alive</option>
                                <option value="dead" ${$(e.relatedTarget).data('status') == 'dead' ? 'selected' : ''}>dead</option>
                                <option value="sold" ${$(e.relatedTarget).data('status') == 'sold' ? 'selected' : ''}>sold</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="fw-semibold">Jenis Kelamin</label>
                            <select name="gender" id="gender" class="form-control">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="male" ${$(e.relatedTarget).data('gender') == 'male' ? 'selected' : ''}>Laki-laki</option>
                                <option value="female" ${$(e.relatedTarget).data('gender') == 'female' ? 'selected' : ''}>Perempuan</option>
                            </select>
                            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="birth_date" class="fw-semibold">Birth Date <span
                                    class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control"
                                id="birth_date" name="birth_date" value="${$(e.relatedTarget).data('birth_date')}">
                            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="birth_weight" class="fw-semibold">Birth Weight<span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control" id="birth_weight" name="birth_weight" value="${$(e.relatedTarget).data('birth_weight')}"
                                placeholder="Masukan Berat" required>
                            <x-input-error :messages="$errors->get('birth_weight')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="birth_height" class="fw-semibold">Birth Height<span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control" id="birth_height" name="birth_height" value="${$(e.relatedTarget).data('birth_height')}"
                                placeholder="Masukan Tinggi" required>
                            <x-input-error :messages="$errors->get('birth_height')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="farm_id" class="fw-semibold">Farm Name <span class="text-danger">*</span></label>
                            <select name="farm_id" id="farm_id"
                                class="form-control {{ $errors->has('farm_id') ? 'is-invalid' : '' }}">
                                <option value="">Select Farm</option>
                                ${farmName}
                            </select>
                            <x-input-error :messages="$errors->get('farm_id')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="iot_device_id" class="fw-semibold">IoT Device <span class="text-danger">*</span></label>
                            <select name="iot_device_id" id="iot_device_id"
                                class="form-control {{ $errors->has('iot_device_id') ? 'is-invalid' : '' }}">
                                <option value="">Select IOT Device</option>
                                ${iotDeviceOptions}
                            </select>
                            <x-input-error :messages="$errors->get('iot_devices_id')" class="mt-2" />
                        </div>
                        <div class="mb-3" id="avatar-upload">
                            <label for="image" class="fw-semibold">Avatar<span
                                    class="ml-1 text-danger">*</span></label>
                            <input type="file" class=" dropify" id="image" name="image" placeholder="Isi file"
                                data-allowed-file-extensions='["png", "jpeg","jpg"]' data-default-file="${$(e.relatedTarget).data('image')}">
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    `;

            $('#modal-content').html(html);

            // Initialize the dropify plugin for file inputs
            $('.dropify').dropify();
            });
            </script>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"
            integrity="sha512-8QFTrG0oeOiyWo/VM9Y8kgxdlCryqhIxVeRpWSezdRRAvarxVtwLnGroJgnVW9/XBRduxO/z1GblzPrMQoeuew=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script>
                $('.dropify').dropify
            </script>

@endsection
