<div class="footer-contact">
  <style>
    .footer-contact {
        background-color: #007dc3;
        color: #fff;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        padding: 30px 40px; /* Giảm padding để giảm chiều cao */
        box-sizing: border-box;
    }

    .footer-contact .column {
        flex: 1 1 300px;
        margin: 10px 15px; /* Giảm margin để tiết kiệm không gian */
    }

    .footer-contact h3 {
        color: #f1c40f;
        font-size: 20px;
        margin-bottom: 10px; /* Giảm khoảng cách dưới tiêu đề */
    }

    .footer-contact p, .footer-contact a {
        font-size: 13px; /* Nhỏ hơn một chút */
        margin: 4px 0;
        color: #fff;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .footer-contact a:hover {
        color: #f1c40f;
    }

    .footer-contact iframe {
        width: 100%;
        height: 150px; /* Giảm chiều cao iframe bản đồ */
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .footer-icon {
        color: #f1c40f;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .footer-contact {
            flex-direction: column;
            padding: 20px;
        }

        .footer-contact .column {
            margin: 10px 0;
        }

        .footer-contact iframe {
            height: 200px;
        }
    }
  </style>

  <div class="column">
    <h3>Thông Tin Liên Hệ</h3>
    <p><i class="fas fa-envelope footer-icon"></i> Email: <a href="mailto:phongtro@example.com">phongtro@example.com</a></p>
    <p><i class="fas fa-phone-alt footer-icon"></i> Hotline: <a href="tel:02383855452">02383855.452</a></p>
    <p><i class="fas fa-map-marker-alt footer-icon"></i> 182 Lê Duẩn, TP. Vinh, Nghệ An</p>
  </div>

  <div class="column">
    <h3>Bản Đồ</h3>
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1730.089542771556!2d105.69455767195709!3d18.65904594910403!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3139cddf0bf20f23%3A0x86154b56a284fa6d!2zVHLGsOG7nW5nIMSQ4bqhaSho4buNYyBWaW5o!5e1!3m2!1svi!2s!4v1747650229056!5m2!1svi!2s"
      loading="lazy"
      allowfullscreen=""
      referrerpolicy="no-referrer-when-downgrade">
    </iframe>
  </div>

  <div class="column">
    <h3>Hỗ Trợ</h3>
    <p><a href="#">Bảng giá dịch vụ</a></p>
    <p><a href="#">Hướng dẫn đăng tin</a></p>
    <p><a href="#">Quy định đăng tin</a></p>
    <p><a href="#">Cơ chế giải quyết tranh chấp</a></p>
    <p><a href="#">Tin tức</a></p>
  </div>
</div>

<!-- Font Awesome cho icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
