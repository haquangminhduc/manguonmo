<div class="contact">
    <div class="map-container">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1730.089542771556!2d105.69455767195709!3d18.65904594910403!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3139cddf0bf20f23%3A0x86154b56a284fa6d!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBWaW5o!5e1!3m2!1svi!2s!4v1747650229056!5m2!1svi!2s"
            width="100%"
            height="350"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

    <div class="contact-info">
        <h3>Thông Tin Liên Hệ</h3>
        <p>Email: phongtro@example.com</p>
        <p>Hotline: 02383855.452</p>
        <p>Địa chỉ: 182 Lê Duẩn, TP. Vinh, Nghệ An</p>
    </div>
</div>

<style>
    .contact {
        background: #333;
        color: #fff;
        padding: 20px;
        margin-top: 20px;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
        box-sizing: border-box;
        display: flex; /* Kích hoạt Flexbox */
        align-items: flex-start; /* Căn các mục theo chiều dọc ở đầu (hoặc center, stretch) */
        gap: 20px; /* Tạo khoảng cách giữa thông tin liên hệ và bản đồ */
    }

    .contact-info {
        flex: 1; /* Cho phép phần thông tin co giãn, chiếm 1 phần không gian */
        /* Hoặc bạn có thể đặt width cụ thể, ví dụ: flex-basis: 40%; */
        text-align: left; /* Căn chữ về bên trái cho dễ đọc, hoặc center nếu muốn */
    }

    .contact h3 { /* Áp dụng cho h3 bên trong .contact-info */
        margin: 0;
        font-size: 24px;
        margin-bottom: 10px;
    }

    .contact p { /* Áp dụng cho p bên trong .contact-info */
        margin: 5px 0;
        font-size: 16px;
    }

    /* CSS cho bản đồ */
    .map-container {
        flex: 1; /* Cho phép bản đồ co giãn, chiếm 1 phần không gian */
        /* Hoặc bạn có thể đặt width cụ thể, ví dụ: flex-basis: 60%; */
        /* margin-top: 15px;  Không cần margin-top ở đây nữa vì đã có gap */
        overflow: hidden;
        position: relative;
        /* width: 100%; Không cần width 100% khi dùng flex item, nó sẽ tự điều chỉnh */
    }

    .map-container iframe {
        border-radius: 5px;
        width: 100%; /* Để iframe chiếm toàn bộ chiều rộng của .map-container */
        height: 300px; /* Điều chỉnh chiều cao mong muốn cho bản đồ */
        display: block; /* Loại bỏ khoảng trống thừa bên dưới iframe */
    }

    /* Responsive: Khi màn hình nhỏ, cho các mục xếp chồng lên nhau */
    @media (max-width: 768px) {
        .contact {
            flex-direction: column; /* Xếp chồng các mục */
            align-items: center; /* Căn giữa các mục khi xếp chồng */
        }
        .contact-info, .map-container {
            flex-basis: auto; /* Reset flex-basis */
            width: 100%; /* Chiếm toàn bộ chiều rộng khi xếp chồng */
            text-align: center; /* Căn giữa nội dung khi xếp chồng */
        }
        .map-container {
            margin-top: 20px; /* Thêm lại khoảng cách khi xếp chồng */
        }
        .contact-info {
            text-align: center; /* Căn giữa chữ khi xếp chồng */
        }
    }
</style>