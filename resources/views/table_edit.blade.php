<!DOCTYPE html>
</html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel Sample App</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="http://benalman.com/code/projects/jquery-throttle-debounce/jquery.ba-throttle-debounce.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
        <script>

            $(document).ready( function () {     
                // Setting up ajax to use our token so it doesn't yell at us
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                // Creating datatable leveraging DataTable API (it's great, if you ever write raw jquery/js, use it)
                $('#pointsTable').DataTable(
                    {
                        rowReorder: false,
                        rowId: "id",
                        columns: [
                            {"data": "name"},
                            {"data": "x"},
                            {"data": "y"},
                        ],
                        ajax: {
                            url: "/points",
                            dataSrc: "data",
                        }
                    }
                );
                
                // Show creation modal
                $('#newPointButton').click(function () {
                    $('#createModal').modal('show');
                })

                // Handle click on row in datatable
                $('#pointsTable').on('click', 'tr', function () {
                    // On reset, we grab the id from the row (tr) we click
                    var id = $(this).attr('id');

                    $.get('points/' + id, function (data) {
                        $('#editModal').modal('show');
                        $('#idEditField').val(data.data.id);
                        $('#nameEditField').val(data.data.name);
                        $('#xEditField').val(data.data.x);
                        $('#yEditField').val(data.data.y);
                        updateRelativePoints()
                    })
                });

                // Handle edit modal form reset
                $('#editResetButton').click(function () {
                    // On reset, we grab the id from the edit modal form because we know it will be there
                    var id = $('#idEditField').val();

                    $.get('points/' + id, function (data) {
                        $('#editModal').modal('show');
                        $('#idEditField').val(data.data.id);
                        $('#nameEditField').val(data.data.name);
                        $('#xEditField').val(data.data.x);
                        $('#yEditField').val(data.data.y);
                        updateRelativePoints();
                    })
                });

                // Handle point editing
                $('#pointDataEdit').on('submit', function (e) {
                    e.preventDefault();
                    var data = {};

                    // Run foreach to get data into dictionary - usually in { name: "", value: ""} format, harder to use
                    $("#pointDataEdit :input").serializeArray().forEach(function (obj) {
                        data[obj.name] = obj.value;
                    });

                    var table = $('#pointsTable').DataTable();

                    $.ajax({
                        url: 'points/' + data.idEditField,
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            name: data.nameEditField,
                            x: data.xEditField,
                            y: data.yEditField,
                        },
                        dataType: 'json',
                        success: function (r) {
                            table.ajax.reload();
                            $('#editErrorMsg').hide();
                            $('#pointDataEdit').trigger("reset");
                            $('#editModal').modal('hide');
                        },
                        error: function (r) {
                            $('#editErrorMsg').html(r?.responseJSON?.message ? r?.responseJSON?.message : "Something isn't right!").show();
                        }
                    });
                });

                // Handle point creation
                $('#pointDataCreate').on('submit', function (e) {
                    e.preventDefault();
                    var data = {};

                    // Run foreach to get data into dictionary - usually in { name: "", value: ""} format, harder to use
                    $("#pointDataCreate :input").serializeArray().forEach(function (obj) {
                        data[obj.name] = obj.value;
                    });

                    var table = $('#pointsTable').DataTable();

                    $.ajax({
                        url: 'points/',
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            name: data.nameCreateField,
                            x: data.xCreateField,
                            y: data.yCreateField,
                        },
                        dataType: 'json',
                        success: function (r) {
                            table.ajax.reload();
                            $('#createErrorMsg').hide();
                            $('#pointDataCreate').trigger("reset");
                            $('#createModal').modal('hide');
                        },
                        error: function (r) {
                            $('#createErrorMsg').html(r?.responseJSON?.message ? r?.responseJSON?.message : "Something isn't right!").show();
                        }
                    });
                });

                // Handle point deletion
                $('#deletePoint').click(function (e) {
                    e.preventDefault();
                    var table = $('#pointsTable').DataTable();

                    $.ajax({
                        url: 'points/' + $('#idEditField').val(),
                        type: "DELETE",
                        data:{
                            '_token': '{{ csrf_token() }}',
                        },
                        dataType: 'json',
                        success: function (r) {
                            table.ajax.reload();
                            $('#editErrorMsg').hide();
                            $('#pointDataEdit').trigger("reset");
                            $('#editModal').modal('hide');
                        },
                        error: function (r) {
                            $('#editErrorMsg').html(r?.responseJSON?.message ? r?.responseJSON?.message : "Something isn't right!").show();
                        }
                    });
                })

                // This function updates the tables with data about relative points
                function updateRelativePoints() {                    
                    var data = {};
                    $("#pointDataEdit :input").serializeArray().forEach(function (obj) {
                        data[obj.name] = obj.value;
                    });

                    $.ajax({
                        url: '/points/getRelatedPoints',
                        type: "GET",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            id: data.idEditField, // Passing this to exclude it from final results
                            x: data.xEditField,
                            y: data.yEditField,
                        },
                        success: function (r) {
                            // Populate content from response; we assume valid response format here
                            let closePoints = r.data.close;
                            let farPoints = r.data.far;
                            
                            $("#closePointsTableHeader").html(closePoints.length > 1 ? "Closest Points" : "Closest Point");
                            $("#farPointsTableHeader").html(farPoints.length > 1 ? "Farthest Points" : "Farthest Point");

                            $("#closePointsTable").find('tbody').empty();
                            $("#farPointsTable").find('tbody').empty();

                            closePoints.forEach(function(item) {
                                $("#closePointsTable").find('tbody').append('<tr><td>'+item.name+'</td><td>'+item.x+'</td><td>'+item.y+'</td><td>'+item.distance+'</td></tr>');
                            });

                            farPoints.forEach(function(item) {
                                $("#farPointsTable").find('tbody').append('<tr><td>'+item.name+'</td><td>'+item.x+'</td><td>'+item.y+'</td><td>'+item.distance+'</td></tr>');
                            });
                        }
                    });

                    
                }


                // Bind update function to changes on x and y edits
                $('#xEditField').on("input", $.debounce(200, updateRelativePoints));
                $('#yEditField').on("input", $.debounce(200, updateRelativePoints));

            } );
        </script>
    </head>
    <body class="antialiased">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Points Table') }}</div>

                        <div class="card-body">
                            <table id="pointsTable" class="display">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>X</th>
                                        <th>Y</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                            <br>
                            <button id="newPointButton" type="button" class="btn btn-secondary" data-dismiss="modal">+ Add Point</button>

                            <div class="modal fade" tabindex="-1" role="dialog" id="editModal">
                                <div class="modal-dialog" role="document">
                                    <form id="pointDataEdit">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Point</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                    <div class="modal-content">
                                                        <p id="editErrorMsg" style="color: red; margin: 20px; font-weight: bold; display: none;"></p>
                                                        <input type="hidden" id="idEditField" name="idEditField" value="">
                                                        <div class="modal-body">
                                                            <label for="nameEditField">Name: </label>
                                                            <input type="text" name="nameEditField" id="nameEditField" value="" class="form-control">
                                                            <br>
                                                            <label for="xEditField">X: </label>
                                                            <input type="text" name="xEditField" id="xEditField" value="" class="form-control">
                                                            <br>
                                                            <label for="yEditField">Y: </label>
                                                            <input type="text" name="yEditField" id="yEditField" value="" class="form-control">
                                                            <br>
                                                            <button id="editResetButton" type="button" class="btn btn-secondary">Reset</button>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <table class="table" id="closePointsTable">
                                                        <h4 id="closePointsTableHeader">Closest Point</h4>
                                                        <thead>
                                                            <tr>
                                                            <th scope="col">Name</th>
                                                            <th scope="col">X</th>
                                                            <th scope="col">Y</th>
                                                            <th scope="col">Distance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>

                                                    <table class="table" id="farPointsTable">
                                                        <h4 id="farPointsTableHeader">Farthest Point</h4>
                                                        <thead>
                                                            <tr>
                                                            <th scope="col">Name</th>
                                                            <th scope="col">X</th>
                                                            <th scope="col">Y</th>
                                                            <th scope="col">Distance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button id="deletePoint" type="button" class="btn btn-danger float-left">Delete</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="modal fade" tabindex="-1" role="dialog" id="createModal">
                                <div class="modal-dialog" role="document">
                                    <form id="pointDataCreate">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Point</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="modal-content">
                                                    <p id="createErrorMsg" style="color: red; margin: 20px; font-weight: bold; display: none;"></p>
                                                    <input type="hidden" id="idCreateField" name="idCreateField" value="">
                                                    <div class="modal-body">
                                                        <label for="nameCreateField">Name: </label>
                                                        <input type="text" name="nameCreateField" id="nameCreateField" value="" class="form-control">
                                                        <br>
                                                        <label for="xCreateField">X: </label>
                                                        <input type="text" name="xCreateField" id="xCreateField" value="" class="form-control">
                                                        <br>
                                                        <label for="yCreateField">Y: </label>
                                                        <input type="text" name="yCreateField" id="yCreateField" value="" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Create</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>