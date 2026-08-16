# Integrasi FreeRADIUS REST

Dokumen ini menjelaskan cara menghubungkan **FreeRADIUS** ke PHPNuxBill menggunakan
**FreeRADIUS REST API**. Dengan metode ini, FreeRADIUS berbicara ke PHPNuxBill melalui
koneksi **HTTP**, sehingga konfigurasi lebih sederhana dan **tidak memerlukan IP Publik**
untuk Mikrotik.

> Catatan: Saat ini mendukung autentikasi **PAP**. CHAP masih dalam pengembangan.

> **⚠️ Keamanan endpoint (wajib):** Endpoint `/api/radius` pada aplikasi ini **dilindungi
> shared secret**. Anda harus:
> 1. Set nilai acak yang panjang pada `RADIUS_API_SECRET` di file `.env` aplikasi Laravel.
> 2. Tambahkan `secret=YOUR_RADIUS_API_SECRET` pada parameter `data` **setiap** section
>    `rest` di bawah ini, dengan nilai yang sama persis dengan `RADIUS_API_SECRET`.
>
> Tanpa secret yang cocok, semua request ditolak dengan HTTP 401 (fail-closed).

> **🔐 Hardening (disarankan):**
> - **Verifikasi sertifikat TLS aplikasi.** Di `mods-enabled/rest`, set `check_cert = yes`
>   dan arahkan `ca_file` ke bundel CA Anda. Jika aplikasi memakai CA intermediate,
>   arahkan `ca_file` ke **CA penerbit (intermediate)** untuk menghindari `CURLE_SSL_ISSUER_ERROR`.
> - **Gunakan secret kuat & unik** untuk setiap `client { }` di `clients.conf` (itu adalah
>   pre-shared key) serta `RADIUS_API_SECRET` acak yang panjang di sisi aplikasi.
> - **Firewall**: hanya izinkan IP server FreeRADIUS menjangkau `/api/radius` aplikasi.
> - **Matikan debug di produksi**: set `APP_DEBUG=false` pada `.env` Laravel agar error
>   tidak membocorkan stack trace ke publik.

---

## 1. Instalasi

Install FreeRADIUS beserta modul REST-nya:

```bash
apt-get -y install freeradius freeradius-rest
```

---

## 2. Konfigurasi Clients

Edit file konfigurasi client:

```bash
nano /etc/freeradius/3.0/clients.conf
```

Tambahkan IP Mikrotik (atau IP Publik) Anda. Contoh — **setiap kali menambah/mengedit
client, restart FreeRADIUS**:

```
client myRouterA {
	ipaddr		= 10.0.1.0/24
	secret		= verysecret
}

client myRouterB {
	ipaddr		= 10.0.2.0/24
	secret		= secretvery
}
```

---

## 3. Buat Konfigurasi REST

Buat/isi file modul REST:

```bash
nano /etc/freeradius/3.0/mods-enabled/rest
```

Gunakan konfigurasi berikut, dan ganti `phpnuxbill.domain` dengan domain Anda:

```
rest {
    tls {
        check_cert = no
        check_cert_cn = no
    }
	connect_uri = "https://phpnuxbill.domain/radius.php"

	authenticate {
		uri = "${..connect_uri}?action=authenticate"
        method = 'post'
        body = 'post'
        data = "username=%{urlquote:%{User-Name}}&password=%{urlquote:%{User-Password}}&nasid=%{urlquote:%{NAS-Identifier}}&CHAPchallenge=%{urlquote:%{CHAP-Challenge}}&CHAPassword=%{urlquote:%{CHAP-Password}}&realm=%{urlquote:%{Mikrotik-Realm}}&macAddr=%{urlquote:%{Calling-Station-Id}}&nasip=%{urlquote:%{NAS-IP-Address}}&secret=YOUR_RADIUS_API_SECRET"
	    tls = ${..tls}
	}

    authorize {
        uri = "${..connect_uri}?action=authorize"
        method = 'post'
        body = 'post'
        data = "username=%{urlquote:%{User-Name}}&password=%{urlquote:%{User-Password}}&nasid=%{urlquote:%{NAS-Identifier}}&CHAPchallenge=%{urlquote:%{CHAP-Challenge}}&CHAPassword=%{urlquote:%{CHAP-Password}}&realm=%{urlquote:%{Mikrotik-Realm}}&macAddr=%{urlquote:%{Calling-Station-Id}}&nasip=%{urlquote:%{NAS-IP-Address}}&secret=YOUR_RADIUS_API_SECRET"
	    tls = ${..tls}
    }

	accounting {
		uri = "${..connect_uri}?action=accounting"
		method = 'post'
		body = 'post'
        data = "username=%{urlquote:%{User-Name}}&nasIpAddress=%{urlquote:%{NAS-IP-Address}}&realm=%{urlquote:%{Mikrotik-Realm}}&nasid=%{urlquote:%{NAS-Identifier}}&acctSessionId=%{urlquote:%{Acct-Session-Id}}&macAddr=%{urlquote:%{Calling-Station-Id}}&acctSessionTime=%{urlquote:%{Acct-Session-Time}}&acctInputOctets=%{urlquote:%{Acct-Input-Octets}}&acctOutputOctets=%{urlquote:%{Acct-Output-Octets}}&acctInputGigawords=%{urlquote:%{Acct-Input-Gigawords}}&acctOutputGigawords=%{urlquote:%{Acct-Output-Gigawords}}&acctInputPackets=%{urlquote:%{Acct-Input-Packets}}&acctOutputPackets=%{urlquote:%{Acct-Output-Packets}}&nasPortId=%{urlquote:%{NAS-Port-Id}}&framedIPAddress=%{urlquote:%{Framed-IP-Address}}&sessionTimeout=%{urlquote:%{Session-Timeout}}&framedIPNetmask=%{urlquote:%{Framed-IP-Netmask}}&acctStatusType=%{urlquote:%{Acct-Status-Type}}&nasPortType=%{urlquote:%{NAS-Port-Type}}&secret=YOUR_RADIUS_API_SECRET"
		tls = ${..tls}
	}

    post-auth {
        uri = "${..connect_uri}?action=post-auth"
        method = 'post'
        body = 'post'
        data = "username=%{urlquote:%{User-Name}}&secret=YOUR_RADIUS_API_SECRET"
		tls = ${..tls}
    }

	pool {
		start = ${thread[pool].start_servers}
		min = ${thread[pool].min_spare_servers}
		max = ${thread[pool].max_servers}
		spare = ${thread[pool].max_spare_servers}
		uses = 0
		retry_delay = 30
		lifetime = 0
		idle_timeout = 60
	}
}
```

---

## 4. Konfigurasi Sites

Edit file sites default:

```bash
nano /etc/freeradius/3.0/sites-enabled/default
```

Konfigurasikan seperti berikut (**jangan hapus bagian lain**), dengan menyisipkan blok `rest`:

```
authorize {
#   filter_username
#	filter_password
#	preprocess
#	operator-name
#	cui
#	auth_log
	rest
    if (ok) {
        update control {
            Auth-Type := rest
        }
    }

    ....
}

authenticate {
    Auth-Type rest {
        rest {
            updated = 1
        }
        if (updated) {
            ok
        }
    }
	Auth-Type rest {
		rest
	}

    ....
}

accounting {
	detail
	rest

    ....
}

session {
	radutmp
    
    ....
}

post-auth {
	if (reply:Group-Name) {
        update control {
            &Group := "%{reply:Group-Name}"
        }
    }
    if (reply:Mikrotik-Rate-Limit) {
        update reply {
            Mikrotik-Rate-Limit := "%{reply:Mikrotik-Rate-Limit}"
        }
    }
    if (reply:Expiration) {
        update reply {
            Expiration := "%{reply:Expiration}"
        }
    }
	update {
		&reply: += &session-state:
	}
}
```

---

## 5. Restart & Debug

Restart layanan:

```bash
systemctl restart freeradius.service
```

**Debug** — jika ada masalah, hentikan FreeRADIUS, aktifkan log radius di Mikrotik,
lalu jalankan FreeRADIUS di foreground:

```bash
systemctl stop freeradius.service
```

```bash
/system logging add topics=radius,debug action=memory
```

```bash
freeradius -X
```

Kemudian coba login dari Mikrotik untuk melihat prosesnya berjalan.
