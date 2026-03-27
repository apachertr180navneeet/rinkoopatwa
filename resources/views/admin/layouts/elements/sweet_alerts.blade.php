
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })
    function setFlesh(status, message = '') {
        Toast.fire({
            icon: status,
            title: message
        })
    }
</script>
@if(Session::has('success'))
<script>
    Toast.fire({
        icon: 'success',
        title: "{{ !empty(Session::get('message')) ? Session::get('message') :Session::get('success') }}"
    })
</script>
@endif
@if(Session::has('error'))
<script>
    Toast.fire({
        icon: 'error',
        title: "{{ !empty(Session::get('message')) ? Session::get('message') :Session::get('error') }}"
    })
</script>
@endif
@if(Session::has('warning'))
<script>
    Toast.fire({
        icon: 'warning',
        title: "{{ !empty(Session::get('message')) ? Session::get('message') :Session::get('warning') }}"
    })
</script>
@endif