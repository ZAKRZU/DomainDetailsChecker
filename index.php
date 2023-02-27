<?php 
session_start();
include 'classes/Manager.php';

$manager = null;

if (isset($_GET['lookup'])) {
    $lookup = $_GET['lookup'];
    $manager = new Manager($lookup);
    if ($manager->isDomainValid()) {
        $mainDomain = $manager->getMainDomain();
        $subDomain = $manager->getSubdomain();
    }
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <div class="mt-2 d-flex flex-row-reverse gap-2">
            <div class="col-auto">
                <select class="form-select" aria-label="Theme selector" id="theme-selector">
                    <option value="dark" selected>Dark</option>
                    <option value="light">Light</option>
                </select>
            </div>
            <div class="col-auto align-self-center"><span><?php echo VERSION ?></span></div>
        </div>
        <div class="mb-3 px-5">
            <h1>SSL CHECKER</h1>
            <form action="" method="GET">
                <div class="row">
                    <div class="col">
                        <label for="domain" class="form-label lead">Type domain name you want to check:</label>
                        <input type="text" name="lookup" class="form-control" value="<?php if ($manager) echo $mainDomain->getHostname() ?>">
                    </div>
                    <div class="col-auto align-self-end">
                        <input id="sendButton" type="submit" class="btn btn-primary" value="Check">
                    </div>
                </div>
            </form>
            <div id="loading" class="mt-3 align-center d-none">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <?php 
            if ($manager)
            if (!$manager->isDomainValid()) {
            ?>
            <div class="row m-3 alert alert-danger">
                <span>BAD DOMAIN</span>
            </div>
        </div>
            <?php } else { ?>
            <div class="row">
                <?php 
                if ($manager->isDBConnected())
                if ($manager->getDomainCheckCount() > 0)
                    echo "<span>This domains has been checked " . $manager->getDomainCheckCount() . 
                        " time! Last time " . $manager->getDomainLastCheckDate() . "</span>";
                else if ($manager->getDomainCheckCount() > 1)
                    echo "<span>This domains has been checked " . $manager->getDomainCheckCount() . 
                        " times! Last time " . $manager->getDomainLastCheckDate() . "</span>";
                else {
                    echo "<span>This domain has not been checked yet!</span>";
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header"><b>DNS</b></div>
                    <div class="card-body">
                        <b>A</b>
                        <div class="container">
                            <div class="row py-2 border-bottom ">
                                <div class="col"><?php echo $mainDomain->getHostname() ?></div>
                                <div class="col"><?php echo $mainDomain->getIP() ?></div>
                                <div class="col"><?php echo $mainDomain->getReverseDNS() ?></div>
                            </div>
                            <div class="row py-2">
                                <div class="col"><?php echo $subDomain->getHostname() ?></div>
                                <div class="col"><?php echo $subDomain->getIP() ?></div>
                                <div class="col"><?php echo $subDomain->getReverseDNS() ?></div>
                            </div>
                        </div>
                        <hr>
                        <b>NS</b>
                        <div class="d-flex gap-2 px-3">
                            <?php 
                                if (count($manager->getNS()) > 0)
                                foreach ($manager->getNS() as $key => $value) {
                                    echo "<p>".$value['target']."</p>";
                                }
                                else {
                                    echo "Nameservers not found";
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <?php if($manager->getSSL()->getDaysNumber() < 0) { ?>
                    <div class="card-header bg-danger-subtle bg-gradient"><b>SSL</b></div>
                    <div class="card-body bg-danger-subtle">
                    <?php } else if ($manager->getSSL()->getDaysNumber() > 0 && $manager->getSSL()->getDaysNumber() < SSL_WARNING_DAYS) { ?>
                    <div class="card-header bg-warning-subtle bg-gradient"><b>SSL</b></div>
                    <div class="card-body bg-warning-subtle">
                    <?php } else { ?>
                    <div class="card-header bg-success-subtle bg-gradient"><b>SSL</b></div>
                    <div class="card-body bg-success-subtle">
                    <?php } ?>
                        <div class="d-flex flex-column gap-2 ">
                                <div class="row">
                                    <div class="col">CN</div>
                                    <div class="col"><?php  echo $manager->getSSL()->getCN() ?></div>
                                </div>
                                <div class="row">
                                    <div class="col">ISSUER</div>
                                    <div class="col"><?php  echo $manager->getSSL()->getIssuer() ?></div>
                                </div>
                                <div class="row">
                                    <div class="col">VALID FROM</div>
                                    <div class="col"><?php  echo $manager->getSSL()->getValidFrom() ?></div>
                                </div>
                                <div class="row">
                                    <div class="col">VALID TO</div>
                                    <div class="col"><?php  echo $manager->getSSL()->getValidTo() ?></div>
                                </div>
                                <div class="row">
                                    <div class="col">DAYS</div>
                                    <div class="col"><?php  echo $manager->getSSL()->getDays() ?></div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <table class="table accordion">
                <thead>
                  <tr>
                    <th scope="col">FROM</th>
                    <th scope="col">TO</th>
                    <th scope="col">HTTPS</th>
                    <th scope="col">SAME DOMAIN</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                <?php 
                    $mdRedirect = $manager->getMainDomain()->getRedirects();
                    if (count($mdRedirect->getAll()) > 0) {
                        echo '<tr data-bs-toggle="collapse" data-bs-target="#mainDomain">';
                        echo '<th scope="row">'.$mdRedirect->getFirst()->getFrom().'</th>';
                        echo '<td>'.$mdRedirect->getLast()->getLocation().'</td>'; // last redirect
                        if ($mdRedirect->hasHTTPS())
                            echo '<td><span class="badge bg-success">YES</span></td>';
                        else
                            echo '<td><span class="badge bg-danger">NO</span></td>';
                        if ($mdRedirect->redirectsToAnotherSite())
                            echo '<td><span class="badge bg-danger">NO</span></td>';
                        else
                            echo '<td><span class="badge bg-success">YES</span></td>';
                        echo '<td><span class="bi bi-chevron-down"></span></td></tr>';
                        foreach ($mdRedirect->getAll() as $key => $value) {
                            echo '<tr class="collapse accordion-collapse bg-secondary" id="mainDomain">';
                            echo '<th scope="row">'.$value->getFrom().'</th>';
                            echo '<td>'.$value->getLocation().'</td>';
                            if ($value->getHasHTTPS())
                                echo '<td><span class="badge bg-success">YES</span></td>';
                            else
                                echo '<td><span class="badge bg-danger">NO</span></td>';
                            if ($value->getAbroad())
                                echo '<td><span class="badge bg-danger">NO</span></td>';
                            else
                                echo '<td colspan="2"><span class="badge bg-success">YES</span></td>';
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr class="bg-danger-subtle">
                        <th scope="row">http://' . $manager->getMainDomain()->getHostname() . '</th>
                        <td colspan="4">NOT FOUND</td>
                      </tr>';
                    }
                ?>
                <?php 
                    $sdRedirect = $manager->getSubdomain()->getRedirects();
                    if (count($sdRedirect->getAll()) > 0) {
                        echo '<tr data-bs-toggle="collapse" data-bs-target="#subDomain">';
                        echo '<th scope="row">'.$sdRedirect->getFirst()->getFrom().'</th>';
                        echo '<td>'.$sdRedirect->getLast()->getLocation().'</td>'; // last redirect
                        if ($sdRedirect->hasHTTPS())
                            echo '<td><span class="badge bg-success">YES</span></td>';
                        else
                            echo '<td><span class="badge bg-danger">NO</span></td>';
                        if ($sdRedirect->redirectsToAnotherSite())
                            echo '<td><span class="badge bg-danger">NO</span></td>';
                        else
                            echo '<td><span class="badge bg-success">YES</span></td>';
                        echo '<td><span class="bi bi-chevron-down"></span></td></tr>';
                        foreach ($sdRedirect->getAll() as $key => $value) {
                            echo '<tr class="collapse accordion-collapse bg-secondary" id="subDomain">';
                            echo '<th scope="row">'.$value->getFrom().'</th>';
                            echo '<td>'.$value->getLocation().'</td>';
                            if ($value->getHasHTTPS())
                                echo '<td><span class="badge bg-success">YES</span></td>';
                            else
                                echo '<td><span class="badge bg-danger">NO</span></td>';
                            if ($value->getAbroad())
                                echo '<td><span class="badge bg-danger">NO</span></td>';
                            else
                                echo '<td colspan="2"><span class="badge bg-success">YES</span></td>';
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr class="bg-danger-subtle">
                        <th scope="row">http://' . $manager->getSubdomain()->getHostname() . '</th>
                        <td colspan="4">NOT FOUND</td>
                      </tr>';
                    }
                ?>
                <?php 
                    $odRedirect = $manager->getMainDomain()->getRedirectsWithUrl();
                    if (count($odRedirect->getAll()) > 0) {
                        echo '<tr data-bs-toggle="collapse" data-bs-target="#otherDomain">';
                        echo '<th scope="row">'.$odRedirect->getFirst()->getFrom().'</th>';
                        echo '<td>'.$odRedirect->getLast()->getLocation().'</td>'; // last redirect
                        if ($odRedirect->hasHTTPS())
                            echo '<td><span class="badge bg-success">YES</span></td>';
                        else
                            echo '<td><span class="badge bg-danger">NO</span></td>';
                        if ($odRedirect->redirectsToAnotherSite())
                            echo '<td><span class="badge bg-danger">NO</span></td>';
                        else
                            echo '<td><span class="badge bg-success">YES</span></td>';
                        echo '<td><span class="bi bi-chevron-down"></span></td></tr>';
                        foreach ($odRedirect->getAll() as $key => $value) {
                            echo '<tr class="collapse accordion-collapse bg-secondary" id="otherDomain">';
                            echo '<th scope="row">'.$value->getFrom().'</th>';
                            echo '<td>'.$value->getLocation().'</td>';
                            if ($value->getHasHTTPS())
                                echo '<td><span class="badge bg-success">YES</span></td>';
                            else
                                echo '<td><span class="badge bg-danger">NO</span></td>';
                            if ($value->getAbroad())
                                echo '<td><span class="badge bg-danger">NO</span></td>';
                            else
                                echo '<td colspan="2"><span class="badge bg-success">YES</span></td>';
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr class="bg-danger-subtle">
                        <th scope="row">http://' . $manager->getMainDomain()->getHostname() . '/random/url</th>
                        <td colspan="4">NOT FOUND</td>
                      </tr>';
                    }
                ?>
                </tbody>
              </table>
        </div>
        <hr>
        <div class="d-flex flex-column">
            <h2>WEBSITE DETAILS</h2>
            <div class="row border-bottom border-top py-2">
                <div class="col">IS WORDPRESS</div>
                <div class="col">
                    <?php 
                    if ($manager->getWordpressUtils()->isWordpress()) {
                        echo '<span class="badge bg-success">YES</span>';
                    } else {
                        echo '<span class="badge bg-danger">NO EVIDENCE FOUND</span>';
                    }
                    ?>
                </div>
            </div>
            <div class="row border-bottom py-2">
                <div class="col">REALLY SIMPLE SSL</div>
                <div class="col">
                    <?php 
                    if ($manager->getWordpressUtils()->hasRSS()) {
                        echo '<span class="badge bg-success">YES</span>';
                    } else {
                        echo '<span class="badge bg-danger">NO EVIDENCE FOUND</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php }?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
        crossorigin="anonymous"></script>
    <script>
        var themeSelector = document.getElementById('theme-selector');
        themeSelector.onchange = () => {
            let theme = document.getElementsByTagName('html')[0].dataset;
            let selected = themeSelector.selectedOptions[0].value;

            if (selected === "light") {
                theme.bsTheme = 'light';
            } else if (selected === 'dark') {
                theme.bsTheme = 'dark';
            }
        }
        var sendButton = document.getElementById('sendButton');
        sendButton.onclick = () => {
            let loading = document.getElementById('loading');
            if (loading.classList.contains('d-none'))
                loading.classList.remove('d-none');
            // else
            //     loading.classList.add('d-none');
        }
    </script>
</body>

</html>