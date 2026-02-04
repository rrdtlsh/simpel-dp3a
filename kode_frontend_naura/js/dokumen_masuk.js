document.addEventListener("DOMContentLoaded", function(){

    // ===== SIDEBAR =====
    const sidebar = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburger");
    hamburger.onclick = () => sidebar.classList.toggle("hide");

    // ===== MODAL DETAIL =====
    const modal = document.getElementById("detailModal");
    const closeBtn = document.getElementById("closeDetail");

    document.querySelectorAll(".btn-view").forEach(btn=>{
        btn.onclick = () => {
            document.getElementById("detailNama").innerText   = btn.dataset.nama;
            document.getElementById("detailBidang").innerText = btn.dataset.bidang;
            document.getElementById("detailTahun").innerText  = btn.dataset.tahun;
            modal.style.display = "block";
        };
    });

    closeBtn.onclick = () => modal.style.display = "none";

    window.onclick = e => {
        if(e.target === modal){
            modal.style.display = "none";
        }
    };

    // ===== EDITOR (MS WORD MINI) =====
    document.querySelectorAll(".editor-toolbar button").forEach(btn=>{
        btn.onclick = ()=>{
            document.execCommand(btn.dataset.cmd,false,null);
        };
    });

    // ===== FILTER =====
    const filterBidang = document.getElementById("filterBidang");
    const filterTahun  = document.getElementById("filterTahun");

    function applyFilter(){
        document.querySelectorAll("#dokumenTable tbody tr").forEach(row=>{
            const cocok =
                (!filterBidang.value || row.dataset.bidang === filterBidang.value) &&
                (!filterTahun.value  || row.dataset.tahun  === filterTahun.value);

            row.style.display = cocok ? "" : "none";
        });
    }

    if(filterBidang && filterTahun){
        filterBidang.onchange = applyFilter;
        filterTahun.onchange  = applyFilter;
    }

});
