// File: Home/js/home.js (Thay thế toàn bộ nội dung trong file này)

//- Khởi tạo chức năng Slideshow và Indicators
function initSlideShow() {
    const imagesWrapper = document.querySelector('.images-wrapper');
    const listPic = document.querySelector('.list-pic');
    if (!imagesWrapper || !listPic) return;

    const images = imagesWrapper.querySelectorAll('img');
    const listImgLen = images.length;
    let currentSlide = 0; 
    let autoSlideInterval;
    const slideDuration = 5000; // 5 giây cho auto slide

    const btnL = document.querySelector("#btn-slide-left");
    const btnR = document.querySelector("#btn-slide-right");
    const indicatorContainer = document.querySelector(".indicator-dots");

    // 1. Hàm chuyển slide chính
    function goToSlide(index) {
        // Dừng auto slide trước khi chuyển thủ công
        clearInterval(autoSlideInterval);
        
        // Đảm bảo index nằm trong giới hạn (vòng lặp vô hạn)
        if (index < 0) {
            index = listImgLen - 1;
        } else if (index >= listImgLen) {
            index = 0;
        }
        currentSlide = index;

        // Tính toán khoảng dịch chuyển: index * chiều rộng của container
        const offset = currentSlide * listPic.clientWidth;
        imagesWrapper.style.transform = `translateX(-${offset}px)`;

        updateIndicators();
        // Khởi động lại auto slide sau khi chuyển thủ công
        startAutoSlide();
    }

    // 2. Tạo và cập nhật chỉ báo (dots)
    function createIndicators() {
        if (!indicatorContainer) return;
        for (let i = 0; i < listImgLen; i++) {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            dot.dataset.index = i;
            dot.addEventListener('click', () => goToSlide(i));
            indicatorContainer.appendChild(dot);
        }
        updateIndicators();
    }

    function updateIndicators() {
        if (!indicatorContainer) return;
        const dots = indicatorContainer.querySelectorAll('.dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
    }

    // 3. Logic Auto Slide
    function startAutoSlide() {
        // Đảm bảo không tạo quá nhiều interval
        clearInterval(autoSlideInterval); 
        autoSlideInterval = setInterval(() => {
            goToSlide(currentSlide + 1);
        }, slideDuration);
    }
    
    // 4. Khởi tạo event listeners
    if (btnR) {
        btnR.addEventListener('click', (e) => {
            e.preventDefault();
            goToSlide(currentSlide + 1);
        });
    }
    if (btnL) {
        btnL.addEventListener('click', (e) => {
            e.preventDefault();
            goToSlide(currentSlide - 1);
        });
    }

    // Cập nhật lại vị trí khi thay đổi kích thước màn hình
    window.addEventListener('resize', () => {
        setTimeout(() => goToSlide(currentSlide), 50); 
    });

    createIndicators();
    startAutoSlide(); 

    setTimeout(() => goToSlide(0), 100); 
}


//- Tạo bộ đếm thời gian cho bảng FLASH_SALE (Giữ nguyên)
function count_Time_FS(){
    let time_FS;
    if (!localStorage.getItem('timeFlashSale')){
        time_FS = new Date().getTime() + 8*24*60*60*1000;
        localStorage.setItem('timeFlashSale', time_FS);
    } else {
        time_FS = parseInt(localStorage.getItem('timeFlashSale'));
    }

    function update_time_FS(){
        let update_time = time_FS - new Date().getTime(); 

        let days = Math.floor(update_time / (1000*24*60*60));
        let hours = Math.floor((update_time % (1000*24*60*60))/ (1000*60*60));
        let minutes = Math.floor((update_time % (1000*60*60))/(1000*60));
        let seconds = Math.floor((update_time % (1000*60))/1000);
        
        const timeElements = document.getElementsByClassName("time");
        if (timeElements.length >= 4) {
             timeElements[0].innerHTML = days < 10 ? "0" + days : days;
             timeElements[1].innerHTML = hours < 10 ? "0" + hours : hours;
             timeElements[2].innerHTML = minutes < 10 ? "0" + minutes : minutes;
             timeElements[3].innerHTML = seconds < 10 ? "0" + seconds : seconds;
        }

        if (update_time < 0) {
            clearInterval(a);
            if (timeElements.length >= 4) {
                 timeElements[0].innerHTML = "00";
                 timeElements[1].innerHTML = "00";
                 timeElements[2].innerHTML = "00";
                 timeElements[3].innerHTML = "00";
            }
            localStorage.removeItem('timeFlashSale');
        }
    }
    let a = setInterval(update_time_FS,1000);
}    


window.addEventListener('load', function() {
    initSlideShow(); 
    count_Time_FS();
});