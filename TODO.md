# TODO - Bugfix pembatalan anggota

- [x] Implement perubahan pada `MembershipRequestController@update()` untuk case pembatalan approval:
  - [x] delete `anggota` (menghapus data anggota dari tabel `anggota`)
  - [x] delete `memberProfile` (jika relasi tersedia)
  - [x] delete `user` (soft delete karena model User pakai SoftDeletes)
  - [x] pastikan urutan operasi aman (load relasi + guard null)
- [ ] Test manual:
  - [ ] buat/ambil anggota
  - [ ] ajukan pembatalan keanggotaan
  - [ ] approve pembatalan
  - [ ] verifikasi baris `anggota` hilang di `/librarian/anggota`
  - [ ] verifikasi data di database (anggota, member_profile, user)


