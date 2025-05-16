<style>
    @media screen and (max-width:796px) {
        .container-mobie {
            padding-left: 20px;
            padding-right: 20px;
        }
    }

    @media screen and (min-width: 990px) {
        #navbarBtnItem {
            display: flex;
            justify-content: flex-end;
        }
    }

    .widgets-icons {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ededed;
        font-size: 26px;
        border-radius: 10px;
    }
</style>
<nav class="navbar navbar-expand-lg shadow-sm bg-white" style="z-index: 99;">
    <div class="container container-mobie">
        <div>
            <a class="navbar-brand fw-bold text-success" href="/hightech/">WCK & IOT</a>
        </div>
        <div>
            <button class="navbar-toggler text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#navbarBtnItem" aria-controls="navbarBtnItem" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon text-dark"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navbarBtnItem">
            <ul class="navbar-nav">
                <!-- <li class="nav-item ps-2 pe-2">
                    <a class="nav-link text-dark" href="/D-CheckIn/"><i class="bi bi-clipboard-data"></i> ลงทะเบียนกิจกรรม</a>
                </li> -->
                <li class="nav-item ps-2 pe-2">
                    <a class="nav-link text-dark" href="/hightech/"><i class="bi bi-house-fill"></i> หน้าแรก</a>
                </li>
                <li class="nav-item ps-2 pe-2">
                    <a class="nav-link text-dark" href="/hightech/watering"><i class="bi bi-droplet-fill"></i> รดน้ำ</a>
                </li>
                <li class="nav-item ps-2 pe-2">
                    <a class="nav-link text-dark" href="/hightech/dayReport"><i class="bi bi-clipboard-data"></i> รายงานประจำวัน</a>
                </li>
                <li class="nav-item ps-2 pe-2">
                    <a class="nav-link text-dark" href="/hightech/monthReport"><i class="bi bi-clipboard-data"></i> รายงานประจำเดือน</a>
                </li>
                <li class="nav-item ps-2 pe-2">
                    <a class="nav-link text-dark" href="/hightech/yearReport"><i class="bi bi-clipboard-data"></i> รายงานประจำปี</a>
                </li>
                <li class="nav-item ps-2 pe-2">
                    <a class="btn btn-danger" href="/hightech/logout"><i class="bi bi-box-arrow-left"></i> ออกจากระบบ</a>
                </li>
            </ul>
        </div>

    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var navLinks = document.querySelectorAll('.nav-item a');
        var currentPath = window.location.pathname;
        navLinks.forEach(function(link) {
            var linkPath = link.getAttribute('href');
            if (linkPath === currentPath) {
                link.classList.remove('text-dark');
                link.classList.add('text-success', 'fw-bold');
            }
        });
    });
</script>