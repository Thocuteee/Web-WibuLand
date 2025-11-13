
//-Cho các ảnh đầu trang chủ trượt đi trượt lại

function slideOpenClose(){
    const imagesWrapper = document.querySelector('.images-wrapper');
    const images = document.querySelectorAll('.images-wrapper img');
    
    let cur  = 0;
    const listImgLen = images.length;
    const imageWid = images[0].clientWidth; // Lấy chiều rộng của một ảnh
    //Có gửi video sự khác nhau của clienWidth và offsetWidth

    // Tạo hàm để chuyển ảnh
    function showNextImage(num) {
        cur+=num;
        if (cur >= listImgLen) {
            cur = 0; // Quay lại ảnh đầu tiên khi đã hết
        }
        else if(cur < 0){
            cur = listImgLen - 1;
        }

        // Cộng trừ khoảng cách để di chuyển ảnh
        imagesWrapper.style.transform = `translateX(-${cur * imageWid}px)`;
    }

    //Chuyển ảnh mỗi 15 giây
    setInterval(showNextImage(1), 15000);
// Nút btn-L and btn-R nhấn vào sẽ di chuyển ảnh
    const btnL =document.querySelector(".fa-chevron-left");
    const btnR =document.querySelector(".fa-chevron-right");

    btnR.addEventListener('click' , () =>{
        showNextImage(1);
    })
    btnL.addEventListener('click',()=>{
        showNextImage(-1);
    })
}

//- Tạo bộ đếm thời gian cho bảng FLASH_SALE
function count_Time_FS(){
    //Kiểm tra coi time_FS có trên localStorage chưa
    let time_FS;
    //Nếu không có key nào tên timeFlashSale thì sẽ tạo một key đó hoặc khi value của key đó nó không giống như num
    if (!localStorage.getItem('timeFlashSale')){
        time_FS = new Date().getTime() + 8*24*60*60*1000;
        localStorage.setItem('timeFlashSale', time_FS);
    } else {
        //Còn nếu thì ta sẽ lấy giá trị của key timeFlashSale gán vào từ chuỗi string sang dạng số time_FS
        time_FS = parseInt(localStorage.getItem('timeFlashSale'));
    }

    //Hàm cập nhật thời gian (Đếm ngược)
    function update_time_FS(){
        let update_time = time_FS - new Date().getTime(); // lấy thời gian từ đã tính từ đầu trừ đi thời gian của năm 

        let days = Math.floor(update_time / (1000*24*60*60));
        let hours = Math.floor((update_time % (1000*24*60*60))/ (1000*60*60));
        let minutes = Math.floor((update_time % (1000*60*60))/(1000*60));
        let seconds = Math.floor((update_time % (1000*60))/1000);

        document.getElementsByClassName("time")[0].innerHTML = days < 10 ? "0" + days : days;
        document.getElementsByClassName("time")[1].innerHTML = hours < 10 ? "0" + hours : hours;
        document.getElementsByClassName("time")[2].innerHTML = minutes < 10 ? "0" + minutes : minutes;
        document.getElementsByClassName("time")[3].innerHTML = seconds < 10 ? "0" + seconds : seconds;

        // Nếu thời gian đã hết, dừng bộ đếm và xóa thời gian khỏi localStorage
        if (update_time < 0) {
            clearInterval(a);
            document.getElementsByClassName("time")[0].innerHTML = "00";
            document.getElementsByClassName("time")[1].innerHTML = "00";
            document.getElementsByClassName("time")[2].innerHTML = "00";
            document.getElementsByClassName("time")[3].innerHTML = "00";
            localStorage.removeItem('timeFlashSale');
        }
    }
    let a = setInterval(update_time_FS,1000);
}    


//- Nhấn vào trái tim trong flash-sale sẽ hiện trái tim màu 


window.addEventListener('load', function() {
    slideOpenClose();
    count_Time_FS();
});