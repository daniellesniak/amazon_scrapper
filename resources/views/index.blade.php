<html>
<head>
    <title>Amazon Scrapper</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
</head>

<body>
<div class="container mt-4">
    <div class="row mt-4">
        <div class="col-12">
            @if (\App\Helpers\Session::getInstance()->has('message'))
                <div class="alert alert-{{ \App\Helpers\Session::getInstance()->get('message')['type'] }}" role="alert">
                    {{ \App\Helpers\Session::getInstance()->get('message')['text'] }}
                </div>

                @php
                    \App\Helpers\Session::getInstance()->forget('message');
                @endphp
            @endif
        </div>
    </div>

    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-get-subcategories-tab" data-toggle="tab" href="#nav-get-subcategories" role="tab" aria-controls="nav-get-subcategories" aria-selected="true">Pobierz podkategorie</a>
            <a class="nav-item nav-link" id="nav-latest-downloads-tab" data-toggle="tab" href="#nav-latest-downloads" role="tab" aria-controls="nav-latest-downloads" aria-selected="false">Ostatnio pobrane</a>
            <a class="nav-item nav-link" id="nav-logs-tab" data-toggle="tab" href="#nav-logs" role="tab" aria-controls="nav-logs" aria-selected="false">Logi</a>
            <a class="nav-item nav-link" id="nav-csv-export-tab" data-toggle="tab" href="#nav-csv-export" role="tab" aria-controls="nav-csv-export" aria-selected="false">Eksportuj do CSV</a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-get-subcategories" role="tabpanel" aria-labelledby="nav-get-subcategories-tab">
            <div class="row mt-4">
                <div class="col-12">
                    <!-- GET SUBCATEGORIES URLS FROM GIVEN CATEGORY -->
                    <h1>Pobierz linki podkategorii</h1>
                    <form action="/getSubcategoriesLinks" method="POST">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Link kategorii</label>
                            <input type="text" class="form-control" id="categoryLink" name="categoryLink" placeholder="Link kategori np. Books">
                        </div>
                        <input type="submit" class="btn btn-primary">
                    </form>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-latest-downloads" role="tabpanel" aria-labelledby="nav-latest-downloads-tab">
            <!-- LATEST FETCHED PRODUCTS -->
            <div class="row mt-4">
                <div class="col-12">
                    <h1>Ostatnio pobrane produkty</h1>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nazwa</th>
                            <th>Kiedy</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($latestProducts as $product)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $product->title }}</td>
                                <td>{{ $product->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-logs" role="tabpanel" aria-labelledby="nav-logs-tab">
            <!-- LOGS -->
            <div class="row mt-4">
                <div class="col-12">
                    <h1>Logi z ostatniego dnia</h1>
                    <textarea readonly class="form-control" rows="20">{{ $logs }}</textarea>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-csv-export" role="tabpanel" aria-labelledby="nav-csv-export-tab">
            <!-- EXPORT TO CSV -->
            <div class="row mt-4">
                <div class="col-12">
                    <h1>Eksportuj do CSV</h1>
                    <form action="/exportToCsv" method="post">
                        <input type="submit" value="Eksportuj!" class="btn btn-default">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- COUNTERS -->
    <div class="row mt-4">
        <div class="col-6 float-left">
            <h6>Pobrane numery ASIN: {{ $asinCount }}</h6>
        </div>
        <div class="col-6">
            <h6 class="float-right">Pobrane produkty: {{ $productsCount }}</h6>
        </div>
    </div>
</div>
</body>
</html>