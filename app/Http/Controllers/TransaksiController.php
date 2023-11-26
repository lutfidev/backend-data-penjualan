<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\DetailTransaksiModel;
use App\Models\TransaksiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;


class TransaksiController extends Controller
{
    public function index()
    {
        $detail_transaksi =  DetailTransaksiModel::with(['barang', 'transaksi'])->get();
        $response = [
            'message' => 'success',
            'data' => $detail_transaksi,
        ];
        return response()->json($response, HttpFoundationResponse::HTTP_OK);
    }

    public function store(Request $request)
    {

        $request->validate([
            'nama_barang' => 'required',
            'jenis_barang' => 'required',
            'stok' => 'required|numeric',
            'jumlah_terjual' => 'required|numeric',
            'tanggal_transaksi' => 'required|date',
        ]);


        $barang = BarangModel::create([
            'nama_barang' => $request->nama_barang,
            'jenis_barang' => $request->jenis_barang,
        ]);

        $transaksi = TransaksiModel::create([
            'stok' => $request->stok,
            'jumlah_terjual' => $request->jumlah_terjual,
            'tanggal_transaksi' => $request->tanggal_transaksi,
        ]);


        $detailTransaksi =  DetailTransaksiModel::create([
            'id_barang' => $barang->id,
            'id_transaksi' => $transaksi->id,
        ]);

        $response = [
            'message' => 'success',
            'data' => $detailTransaksi,
        ];

        return response()->json($response, HttpFoundationResponse::HTTP_CREATED);
    }

    public function edit(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'nama_barang' => 'required',
            'jenis_barang' => 'required',
            'stok' => 'required|numeric',
            'jumlah_terjual' => 'required|numeric',
            'tanggal_transaksi' => 'required|date',
        ]);

        $detail_transaksi = DetailTransaksiModel::find($request->id);

        $barang = BarangModel::find($detail_transaksi->id_barang);

        $transaksi = TransaksiModel::find($detail_transaksi->id_transaksi);

        $barang->update([
            'nama_barang' => $request->nama_barang,
            'jenis_barang' => $request->jenis_barang,
        ]);

        $transaksi->update([
            'stok' => $request->stok,
            'jumlah_terjual' => $request->jumlah_terjual,
            'tanggal_transaksi' => $request->tanggal_transaksi,
        ]);

        $detail_transaksi->update([
            'id_barang' => $barang->id,
            'id_transaksi' => $transaksi->id,
        ]);

        $response = [
            'message' => 'success',
            'data' => $transaksi,
        ];

        return response()->json($response, HttpFoundationResponse::HTTP_CREATED);
    }

    public function delete(Request $request)
    {
        // Find the transaction
        $detail_transaksi = DetailTransaksiModel::find($request->id);
        $id_barang = $detail_transaksi->id_barang;
        // $transaksi = TransaksiModel::find($detail_transaksi->first->id_transaksi);
        // $barang = BarangModel::find($transaksi->first->id_barang);

        if (!$detail_transaksi) {
            return response()->json(['message' => 'Transaction not found'], HttpFoundationResponse::HTTP_NOT_FOUND);
        }

        // Delete the transaction

        $detail_transaksi->delete();
        TransaksiModel::find($detail_transaksi->id_transaksi)->delete();
        BarangModel::find($id_barang)->delete();
        // $barang->delete();
        // $transaksi->delete();


        $response = [
            'message' => 'success',
            'data' => $detail_transaksi,
        ];

        return response()->json($response, HttpFoundationResponse::HTTP_OK);
    }

    // Buatlah backend & frontend dengan adanya fitur searching dan bisa mengurutkan data berdasarkan nama barang, tanggal transaksi
    public function searchAndSort(Request $request)
    {
        $query = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.id_barang', '=', 'barang.id')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->select('detail_transaksi.*', 'barang.*', 'transaksi.*');

        // Search by Nama Barang
        if ($request->has('search')) {
            $query->where('barang.nama_barang', 'like', '%' . $request->input('search') . '%');
        }

        // // Sort
        $sortField = $request->input('sort');

        // // Check if the sort order is specified
        $sortOrder =  $request->input('order');

        if ($sortOrder === 'tanggal_transaksi') {
            $query->orderBy('transaksi.tanggal_transaksi', $sortField);
        } elseif ($sortOrder === 'nama_barang') {
            $query->orderBy('barang.nama_barang', $sortField);
        }

        $result = $query->get();

        $response = [
            'message' => 'success',
            'data' => $result,
        ];

        return response()->json($response, HttpFoundationResponse::HTTP_OK);
    }

    // Buatlah backend & frontend untuk membandingkan jenis barang dengan menampilkan data transaksi terbanyak terjual atau terendah
    public function compareSales(Request $request)
    {

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $mostSoldItem = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.id_barang', '=', 'barang.id')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate, $endDate])
            ->select('barang.nama_barang', DB::raw('SUM(transaksi.jumlah_terjual) as total_terjual'))
            ->groupBy('barang.nama_barang')
            ->orderByDesc('total_terjual')
            ->first();

        $leastSoldItem = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.id_barang', '=', 'barang.id')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate, $endDate])
            ->select('barang.nama_barang', DB::raw('SUM(transaksi.jumlah_terjual) as total_terjual'))
            ->groupBy('barang.nama_barang')
            ->orderBy('total_terjual')
            ->first();

        $response = [
            'message' => 'success',
            'most_sold_item' => $mostSoldItem,
            'least_sold_item' => $leastSoldItem,
        ];

        return response()->json($response, HttpFoundationResponse::HTTP_OK);
    }
}
