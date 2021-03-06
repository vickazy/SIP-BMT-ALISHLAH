@extends('layouts.masbar')
@section('title','Data Absensi')
@section('content')

  <section class="content">
  @include('includes.messages')
      <div class="row">
        <div class="col-xs-12">
          @if($cek == 0)
          <strong>Tidak ada absensi untuk ditampilkan</strong>
          @else
          <h4>Absensi : <strong><a href="{{route('karyawan.edit', ['edit' => $user->id])}}">{{$user->detail->nama}}</a></strong></h4>
          @if(Auth::user()->level == 'hrd' || Auth::user()->level == 'pc' || Auth::user()->level == 'admin')
            <a href="{{route('absen.add', ['id' => $user->id])}}"><button class="btn btn-success btn-flat"><i class="fa fa-plus"></i> Tambah Absensi</button></a>
            <button class="btn btn-warning btn-flat" onclick="cuti_open('{{$user->id}}', '{{$user->gaji->gapok}}', '{{$user->gaji->um}}', '{{$user->gaji->transport}}')"><i class="fa fa-plus"></i> Posting Absensi</button>
            <button class="btn btn-primary btn-flat" id="btnRefresh" onclick="refresh({{$user->id}})"><i class="fa fa-refresh"></i> Refresh Absensi</button>
            <span id="rfStatus" style="display: none;">
              <img src="{{url('loading.svg')}}" alt=""> <span id="rfMessage">Mohon tunggu sampai proses selesai... Proses ini emang lama..</span>
            </span>
                <br/><br/>
          @endif
      
          @endif
          @foreach($bulan as $bulan)
          <?php
                $absen = App\Absen::join('bulan','absen.bulan_id','=','bulan.bulan_id')
                          ->where('user_id', $user_id)
                          ->where('absen.bulan_id', $bulan->bulan_id)
                          ->get();
                $no=1;
                ?>
          @if($absen->count() !== 0)
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Data Absen Bulan <strong>{{ $bulan->nama_bln }}</strong></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive">
              <table id="example" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th width="10vh">No</th>
                  <th>Taggal</th>
                  <th>Jam Masuk</th>
                  <th>Jam Pulang</th>
                  <th>Jam Ijin</th>
                  <th>Jam Kembali</th>
                  <th>Total Jam Kerja</th>
                  <th>Total Menit Kerja</th>
                  <th width="100vh">Keterangan</th>
                  @if(Auth::user()->level !== 'user')
                  <th width="150vh">Aksi</th>
                  @endif
                </tr>
                </thead>
                <tbody>
                
                  @foreach($absen as $absen)
                  <tr>
                    <td>{{$no++}}</td>
                    <td>{{$absen->tgl}}</td>
                    <td>{{$absen->jam_in}}</td>
                    <td>{{$absen->jam_out}} @if(is_null($absen->jam_out)) <i><strong>Belum Absen</strong></i> @endif</td>
                    <td>{{$absen->out_ijin}} @if(is_null($absen->out_ijin)) <i><strong>Belum Ijin</strong></i> @endif</td>
                    <td>{{$absen->in_ijin}} @if(is_null($absen->in_ijin)) <i><strong>Belum Ijin</strong></i> @endif</td>
                    <td><strong>{{$absen->jam_kerja}}</strong> @if(is_null($absen->jam_kerja)) <i><strong>-</strong></i> @endif Jam</td>
                    <td><strong>{{$absen->menit_kerja}}</strong> @if(is_null($absen->menit_kerja)) <i><strong>-</strong></i> @endif Menit</td>
                    <td>{{$absen->kt_ijin}} @if(is_null($absen->kt_ijin)) <i><strong>-</strong></i> @endif</td>
                    @if(Auth::user()->level !== 'user')
                    <td><a href="{{route('absen.edit', ['id' => $absen->id])}}"><button class="btn btn-primary"><i class="fa fa-edit"></i> Edit</button></a>
                    <a href="{{route('absen.get-del', ['id' => $absen->id])}}" onclick="return confirm('Hapus absensi ini?');"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a>
                    </td>
                    @endif
                  </tr>
                  @endforeach
                
                <!-- disini -->
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
          @endif
          @endforeach
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      <!-- Modal -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">×</span></button>
      <h4 class="modal-title">KONFIRMASI SEBELUM POSTING</h4>
    </div>
    <div class="modal-body">
     
        <div class="box-body">
          <div class="form-group">
          <label for="">PILIH BULAN</label>
          <select name="bulan" id="bulan" class="form-control pull-right">
            @foreach($month as $bln)
              <option value="{{$bln->bulan_id}}">{{$bln->nama_bln}}</option>
          @endforeach
          </select>
          <div class="form-group">
          <label for="">MASUKAN TANGGAL (format: mm/dd/yyyy. Cth: 12/27/2017)</label>
          <input type="text" class="form-control pull-right" placeholder="Cth: 07/04/2016 - 10/04/2016" id="tgl-absen" required="1" name="tgl-absen">
          </div>
          <div class="form-group">
          <label for="">GAJI POKOK</label>
          <input type="text" class="form-control pull-right" name="gapok" id="gapok" required="1">
          </div>
          <div class="form-group">
          <label for="">UANG MAKAN</label>
          <input type="text" class="form-control pull-right" name="u_makan" id="u_makan" required="1">
          </div>
          <div class="form-group">
          <label for="">UANG TRANSPORT</label>
          <input type="text" class="form-control pull-right" name="u_transport" id="u_transport" required="1">
          </div>
          </div>
          <div class="info">
            <ul id="info">
              
            </ul>
        </div>
      
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-default pull-left" data-dismiss="modal">BATAL</button>
      <button class="btn btn-success" id="proses-posting">VALIDASI</button>
      <button class="btn btn-primary" id="simpan-posting" disabled="1">POSTING</button>
    </div>
  </div>
  <!-- /.modal-content -->
</div>
</div>
</div>
@endsection
@section('script')
<script src="{{URL::to('dist/sweetalert.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{URL::to('dist/sweetalert.css')}}">
@endsection
@section('bottom-script')
<!-- date-range-picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="{{URL::to('admin/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- bootstrap datepicker -->
<script src="{{URL::to('admin/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<!-- bootstrap time picker -->
<script src="{{URL::to('admin/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
<!-- Page script -->
<script>
$(function () {
    //Date range picker
    $('#tgl-absen').daterangepicker();
    //Date range picker with time picker
    $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY h:mm A'});

    //Date picker
    $('#datepicker').datepicker({
      autoclose: true
    });
    $('#datepicker').datepicker({ dateFormat: 'yy-mm-dd' }).val();

    //Timepicker
    $(".timepicker").timepicker({
      showInputs: false
    });
  });
function refresh(user_id){
  var id = user_id;
  $('#btnRefresh').html("Processing..");
  $('#btnRefresh').prop('disabled', true);
  $('#rfStatus').show('1000');
  $.ajax({
    url: '{{route('refresh.absen')}}',
    type: 'POST',
    data: {_token: '{{csrf_token()}}', act: 'refresh', user_id: id},
    success: function(data){
        var msg = $.parseJSON(data);
        // Parsing data
        $(msg).each(function(index, el) {
            // tampilkan
            var type = el.type;
            var message = el.message;
            $('#rfMessage').html("<strong>"+type+"</strong> : "+message);
            
        });
    }
  })
  .done(function() {
    $('#btnRefresh').html("<i class='fa fa-refresh'></i> Refresh selesai");
    $('#btnRefresh').prop('disabled', false);
    $('#rfStatus').hide('1000');
    reload_interval(2000);
  })
  .fail(function() {
    console.log("error");
  })
  .always(function() {
    console.log("complete");
  });
  
}
function reload_interval(time){
  setInterval(function(){
                
                   location.reload();
                  }, time);
}
function p_awal(){
    $('#proses-posting').html("Memproses..");
    $('#info').html("Sedang memproses mohon tunggu..");
    $('#proses-posting').prop("disabled", true);
    $('body').css('cursor', 'progress');
}
function p_akhir(){
  $('#proses-posting').html("Proses");
  $('#proses-posting').prop("disabled", false);
  $('body').css('cursor', 'default');
}
function cuti_open(user_id, gapok, u_makan, u_transport) {
  var user_id = $.trim(user_id);
  var gapok = $.trim(gapok);
  var u_makan = $.trim(u_makan);
  var u_transport = $.trim(u_transport);


  $('#gapok').val(gapok);
  $('#u_makan').val(u_transport);
  $('#u_transport').val(u_makan);
  $('#modal-edit').modal('show');
  $('#proses-posting').click(function(){
    if ($('#tgl-absen').val() == '') {
      alert("Silahkan pilih tanggal priode!");
      return;
    }
    p_awal();
    var tgl = $('#tgl-absen').val();
    var new_gapok = $('#gapok').val();
    var new_u_makan = $('#u_makan').val();
    var new_u_transport = $('#u_transport').val();
    // Kirim Data POST dengan AJAX
    $.ajax({
      url: '/cek-posting',
      type: 'POST',
      data: {_token: '{{csrf_token()}}',
              user_id: user_id,
              act: 'cek',
              gapok: new_gapok,
              tgl: tgl,
              u_makan: new_u_makan,
              u_transport: new_u_transport},
      success : function(data){
        /*var result = jQuery.parseJSON(msg);*/
        var msg = $.parseJSON(data);
        // Parsing data
        $(msg).each(function(index, el) {
            // tampilkan
            var type = el.type;
            var message = el.message;
            $('#info').html("<strong>"+type+"</strong> : "+message);
            if (type == 'success') {
              $('#simpan-posting').prop("disabled", false);
            } else {
              window.open('{{url('error-message?message=')}}<strong>'+type+'</strong> : '+message, 'Terdapat absensi yang tidak valid', 'width=600,height=400');
              $('#simpan-posting').prop("disabled", true);
            }
              p_akhir();
        });
       
      }
    })
    .done(function() {
      console.log("success");
    })
    .fail(function() {
      console.log("error");
    });
    
  });
  $('#simpan-posting').click(function(){
    if ($('#tgl-absen').val() == '') {
      alert("Silahkan pilih tanggal priode!");
      return;
    }
    p_awal();
    var tgl = $('#tgl-absen').val();
    var bln = $('#bulan').val();
    var new_gapok = $('#gapok').val();
    var new_u_makan = $('#u_makan').val();
    var new_u_transport = $('#u_transport').val();
    // Kirim Data POST dengan AJAX
    $.ajax({
      url: '/cek-posting',
      type: 'POST',
      data: {_token: '{{csrf_token()}}',
              user_id: user_id,
              act: 'post',
              gapok: new_gapok,
              tgl: tgl,
              bln : bln,
              u_makan: new_u_makan,
              u_transport: new_u_transport},
      success : function(data){
        /*var result = jQuery.parseJSON(msg);*/
        var msg = $.parseJSON(data);
        // Parsing data
        $(msg).each(function(index, el) {
            // tampilkan
            var type = el.type;
            var message = el.message;
            $('#info').html("<strong>"+type+"</strong> : "+message);
            if (type == 'success') {
              // enable tombol simpan
              $('#simpan-posting').prop("disabled", false);
            } else if(type == 'complete'){
                alert(message)
              // reload halaman setelah 2 detik
                reload_interval(2000);
            } else {
              $('#simpan-posting').prop("disabled", true);
            }
              p_akhir();
        });
       
      }
    })
    .done(function() {
      console.log("success");
    })
    .fail(function() {
      console.log("error");
    });
    
  });
  
}

</script>
@endsection

