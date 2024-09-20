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
            ['title' => 'Data Post', 'link' => '/antrian/periksa', 'active' => true],
        ];
    @endphp


    <div class="container-fluid">
        {{-- <x-jumbotroon :title="'Data Farm'" :breadcrumbs="$breadcrumbs" /> --}}

        <br>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>Data Post</h3>
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
                                <th>Title</th>
                                <th>Content</th>
                                <th>Images</th>
                                <th>Owner</th>
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
                        <h5 class="modal-title" id="staticBackdropLabel">Buat Post</h5>
                        <small id="emailHelp" class="form-text text-muted">Field dengan tanda <span
                                class="text-danger">*</span> wajib diisi.</small>
                    </div>
                </div>
                <form action="{{ route('blog.store') }}" id="buatBlog" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="fw-semibold">Title <span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                                id="title" name="title" placeholder="Masukan Title Post">
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="content" class="fw-semibold">Content <span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('content') ? 'is-invalid' : '' }}"
                                id="content" name="content" placeholder="Masukan content">
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>
                        <div class="mb-3">
                            <label for="image" class="fw-semibold">Image <span
                                    class="ml-1 text-danger">*</span></label>
                            <input type="file" class="form-control {{ $errors->has('image') ? 'is-invalid' : '' }}"
                                id="image" name="image" placeholder="Masukan image">
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="user_id" class="fw-semibold">Owner <span class="ml-1 text-danger">*</span></label>
                            <select name="user_id" id="user_id"
                                class="form-control {{ $errors->has('user_id') ? 'is-invalid' : '' }}">
                                <option value="">Pilih Owner</option>
                                @foreach ($owners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->name}}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />

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
                ajax: "{{ route('blog.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'content',
                        name: 'content'
                    },
                    {
                        data: 'image',
                        name: 'image'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
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

        $('#updateData').on('shown.bs.modal', function(e) {

            var owners = @json($owners);
            var user_id = $(e.relatedTarget).data('user_id');

            var ownerOptions = owners.map(function(owner) {
                return `<option value="${owner._id}" ${owner._id == user_id ? 'selected' : ''}>${owner.name}</option>`;
            });


            var html = `
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="staticBackdropLabel">Edit Postrm</h5>
                        <small id="emailHelp" class="form-text text-muted">Field dengan tanda <span class="text-danger">*</span> wajib diisi.</small>
                    </div>
                </div>
            <form action="${$(e.relatedTarget).data('url')}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                            <label for="name" class="fw-semibold">Name <span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="${$(e.relatedTarget).data('name')}"
                                id="name" name="name" placeholder="Masukan Name">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="location" class="fw-semibold">Location <span class="ml-1 text-danger">*</span></label>
                            <input type="text" class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" value="${$(e.relatedTarget).data('location')}"
                                id="location" name="location" placeholder="Masukan Location">
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="mb-3">
                            <label for="user_id" class="fw-semibold">Owner <span class="ml-1 text-danger">*</span></label>
                            <select name="user_id" id="user_id"
                                class="form-control {{ $errors->has('user_id') ? 'is-invalid' : '' }}">
                                <option value="">Pilih Owner</option>
                            ${ownerOptions}
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />

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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"
        integrity="sha512-8QFTrG0oeOiyWo/VM9Y8kgxdlCryqhIxVeRpWSezdRRAvarxVtwLnGroJgnVW9/XBRduxO/z1GblzPrMQoeuew=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $('.dropify').dropify();
    </script>
@endsection
