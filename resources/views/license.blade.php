<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="keywords" content="ioncube" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name="description" content="ioncube project" />
    <meta http-equiv="content-language" content="en-us" />
    <meta name="copyright" content="© 2023 Project" />
    <meta name="author" content="Murilo Chianfa" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="revisit-after" content="7 days" />
    <meta name="googlebot" content="noindex">
    <meta name="rating" content="general" />
    <meta name="robots" content="noindex">
    <meta charset="utf-8" />
    <title>Missing License | Project</title>
</head>
<body class="pace-top theme-red">
    <div id="app" class="app app-full-height app-without-header">
        <div class="error-page">
            <div class="error-page-content">
                <div class="login-logo mb-4">
                    <h1><b>Project</b> - {{ $title ?? 'Title' }}</h1>
                </div>
                <div class="card">
                    <div class="card-body login-card-body">
                        <p class="login-box-msg">
                            <span style="font-size: 16px;">{{ $subtitle ?? 'Subtitle' }}</span>
                        </p>
                        <form action="/ioncube.php" enctype="multipart/form-data" method="POST">
                            <div class="input-group mb-3">
                                <input type="file" id="license" name="license" class="form-control">
                                <button type="submit" class="btn btn-primary btn-block">Enviar</button>
                            </div>
                            <div class="row">
                                <div class="col-12 text-center">
                                    <hr />
                                    <button class="btn btn-primary" onclick="download()">
                                        <i class="fa fa-arrow-down"></i>
                                        Hardware ID
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script defer>
        function download() {
            let element = document.createElement("a");
            let licenseData = encodeURI(`{{ $server ?? \ioncube_license_properties() }}`);

            element.setAttribute("href", "data:text/plain;charset=utf-8," + licenseData);
            element.setAttribute("download", "hardware-id.pem");

            element.style.display = "none";
            document.body.appendChild(element);

            element.click();

            document.body.removeChild(element);
        }
    </script>
</body>
</html>