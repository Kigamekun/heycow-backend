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
            ['title' => 'Data Farm', 'link' => '/antrian/periksa', 'active' => true],
        ];
    @endphp


    <div class="container-fluid">
        {{-- <x-jumbotroon :title="'Data Farm'" :breadcrumbs="$breadcrumbs" /> --}}

        <br>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>Data User</h3>
                    <div>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createData">
                            Tambah Data
                        </button>
                    </div>
                </div>
                <br>

                <div class="table-responsive">
                    <table id="datatable-table" class="mt-3 mb-3 rounded-sm table borrder-none table-bordered table-md "
                        style="border-top: none">
                        <thead>
                            <tr style="border-top-width:0.01px">
                                <th>#</th>
                                <th>Avatar</th> <!-- Added Avatar column -->
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone Number</th>
                                <th>Role</th>
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


    <!-- Modal -->
    <div class="modal fade" id="updateData" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="updateDataLabel" aria-hidden="true">
        <div class="modal-dialog" id="updateDialog">
            <div id="modal-content" class="modal-content">
                <div class="modal-body">
                    Loading..
                </div>
            </div>
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="createData" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true" aria-hidden="true">
        <div class="modal-dialog">
            <div id="modal-content" class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="staticBackdropLabel">Buat Farm</h5>
                        <small id="emailHelp" class="form-text text-muted">Field dengan tanda <span
                                class="text-danger">*</span> wajib diisi.</small>
                    </div>
                </div>
                <form action="{{ route('user.store') }}" id="buatFarm" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="fw-semibold">Nama<span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Masukan Nama" required>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="email" class="fw-semibold">Email<span class="ml-1 text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Masukan Email" required>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="password" class="fw-semibold">Password<span
                                    class="ml-1 text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Masukan Password" required>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="role" class="fw-semibold">Role<span class="ml-1 text-danger">*</span></label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="">Pilih Role</option>
                                <option value="admin">Admin</option>
                                <option value="cattleman">Cattleman</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="fw-semibold">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number"
                                placeholder="Masukan Nomor Telepon">
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="address" class="fw-semibold">Alamat</label>
                            <input type="text" class="form-control" id="address" name="address"
                                placeholder="Masukan Alamat">
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="fw-semibold">Jenis Kelamin</label>
                            <select name="gender" id="gender" class="form-control">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="fw-semibold">Biografi</label>
                            <textarea name="bio" id="bio" class="form-control" placeholder="Masukan Biografi"></textarea>
                            <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="avatar" class="fw-semibold">Avatar</label>
                            <input type="file" class="form-control" id="avatar" name="avatar">
                            <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection



@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.bootstrap4.js"></script>

    <script>
        $(function() {
            var table = $('#datatable-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user.index') }}", // Ensure this route returns your user data
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                    },
                    {
                        data: 'avatar',
                        name: 'avatar',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'phone_number',
                        name: 'phone_number'
                    },
                    {
                        data: 'role',
                        name: 'role'
                    },
                    {
                        data: 'action', // Action buttons (Edit/Delete)
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

        $('#updateData').on('shown.bs.modal', function(e) {
            // Dynamically get owner options
            var ownerOptions = '';
            var owners = @json($owners); // Assuming you passed the list of owners as $owners
            var selectedOwner = $(e.relatedTarget).data('user_id');

            owners.forEach(function(owner) {
                var isSelected = owner.id == selectedOwner ? 'selected' : '';
                ownerOptions += `<option value="${owner.id}" ${isSelected}>${owner.name}</option>`;
            });

            // HTML form structure for the modal content
            var html = `
        <div class="modal-header">
            <div>
                <h5 class="modal-title" id="staticBackdropLabel">Edit Farm</h5>
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
                            <label for="email" class="fw-semibold">Email<span class="ml-1 text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="${$(e.relatedTarget).data('email')}"
                                placeholder="Masukan Email" required>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="password" class="fw-semibold">Password<span
                                    class="ml-1 text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Masukan Password" >
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="role" class="fw-semibold">Role<span class="ml-1 text-danger">*</span></label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="">Pilih Role</option>
                                <option value="admin" ${$(e.relatedTarget).data('role') == 'admin' ? 'selected' : ''}>Admin</option>
                                <option value="cattleman" ${$(e.relatedTarget).data('role') == 'cattleman' ? 'selected' : ''}>Cattleman</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="fw-semibold">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="${$(e.relatedTarget).data('phone_number')}"
                                placeholder="Masukan Nomor Telepon">
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="address" class="fw-semibold">Alamat</label>
                            <textarea class="form-control" id="address" name="address" placeholder="Masukan Alamat">${$(e.relatedTarget).data('address')}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
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
                            <label for="bio" class="fw-semibold">Biografi</label>
                            <textarea name="bio" id="bio" class="form-control" placeholder="Masukan Biografi">${$(e.relatedTarget).data('bio')}</textarea>
                            <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                        </div>

                        <div class="mb-3" id="avatar-upload">
                            <label for="avatar" class="fw-semibold">Avatar<span
                                    class="ml-1 text-danger">*</span></label>
                            <input type="file" class=" dropify" id="avatar" name="avatar" placeholder="Isi file"
                                data-allowed-file-extensions='["png", "jpeg","jpg"]' data-default-file="${$(e.relatedTarget).data('avatar')}">
                            <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                        </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    `;

            // Insert the form into the modal
            $('#modal-content').html(html);

            // Initialize the dropify plugin for file inputs
            $('.dropify').dropify();
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"
        integrity="sha512-8QFTrG0oeOiyWo/VM9Y8kgxdlCryqhIxVeRpWSezdRRAvarxVtwLnGroJgnVW9/XBRduxO/z1GblzPrMQoeuew=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $('.dropify').dropify();
    </script>
@endsection
