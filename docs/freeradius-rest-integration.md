FreeRadius Rest
iBNu Maksum edited this page on Oct 9, 2024 · 6 revisions
Freeradius REST API is configuration to make Freeradius talk to PHPNuxBill via HTTP Connection, configuration is more simple.

With Freeradius REST, it will use current PHPNuxBill Core, and you don't need IP Public for Mikrotik.

For now is only support PAP Autentication, i am still working with CHAP

> **⚠️ Endpoint security (required):** The app's `/api/radius` endpoint is protected by a
> shared secret. You MUST:
> 1. Set a long random value for `RADIUS_API_SECRET` in the Laravel app's `.env`.
> 2. Append `secret=YOUR_RADIUS_API_SECRET` to the `data` payload of **every** `rest`
>    section below, using the exact same value as `RADIUS_API_SECRET`.
>
> Without a matching secret every request is rejected with HTTP 401 (fail-closed).

> **🔐 Hardening (recommended):**
> - **Verify the app's TLS certificate.** In `mods-enabled/rest`, set `check_cert = yes`
>   and point `ca_file` to your CA bundle. If the app uses an intermediate CA, point
>   `ca_file` to the **issuing (intermediate)** CA to avoid `CURLE_SSL_ISSUER_ERROR`.
> - **Use a strong, unique secret** for every `client { }` in `clients.conf` (it is a
>   pre-shared key) and a long random `RADIUS_API_SECRET` on the app side.
> - **Firewall**: only allow the FreeRADIUS server's IP to reach the app's `/api/radius`.
> - **Disable debug in production**: set `APP_DEBUG=false` in the Laravel `.env` so
>   errors never leak stack traces to the public.

Installing freeradius
apt-get -y install freeradius freeradius-rest
edit clients.conf

nano /etc/freeradius/3.0/clients.conf
Add your Mikrotik IP or your Public IP, this is example, everytime you add/edit Client, restart freeradius

client myRouterA {
	ipaddr		= 10.0.1.0/24
	secret		= verysecret
}

client myRouterB {
	ipaddr		= 10.0.2.0/24
	secret		= secretvery
}
Create rest Configuration

nano /etc/freeradius/3.0/mods-enabled/rest
use this configuration, change phpnuxbill.domain with your domain

rest {
    tls {
        # ca_file = ${certdir}/ca.pem
		# ca_info_file = ${certdir}/cacert_bundle.pem
		# ca_path = ${certdir}
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
        data = "username=%{urlquote:%{User-Name}}&nasIpAddress=%{urlquote:%{NAS-IP-Address}}&realm=%{urlquote:%{Mikrotik-Realm}}&nasid=%{urlquote:%{NAS-Identifier}}\
&acctSessionId=%{urlquote:%{Acct-Session-Id}}&macAddr=%{urlquote:%{Calling-Station-Id}}&acctSessionTime=%{urlquote:%{Acct-Session-Time}}\
&acctInputOctets=%{urlquote:%{Acct-Input-Octets}}&acctOutputOctets=%{urlquote:%{Acct-Output-Octets}}\
&acctInputGigawords=%{urlquote:%{Acct-Input-Gigawords}}&acctOutputGigawords=%{urlquote:%{Acct-Output-Gigawords}}\
&acctInputPackets=%{urlquote:%{Acct-Input-Packets}}&acctOutputPackets=%{urlquote:%{Acct-Output-Packets}}\
&nasPortId=%{urlquote:%{NAS-Port-Id}}&framedIPAddress=%{urlquote:%{Framed-IP-Address}}\
&sessionTimeout=%{urlquote:%{Session-Timeout}}&framedIPNetmask=%{urlquote:%{Framed-IP-Netmask}}\
&acctStatusType=%{urlquote:%{Acct-Status-Type}}&nasPortType=%{urlquote:%{NAS-Port-Type}}&secret=YOUR_RADIUS_API_SECRET"
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
edit sites

nano /etc/freeradius/3.0/sites-enabled/default 
Configure just like this, don't delete others

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


restart services

systemctl restart freeradius.service
DEBUG
To check if someting wrong, stop freeradius

systemctl stop freeradius.service
Activate log in Mikrotik

/system logging add topics=radius,debug action=memory
run radius just like this

freeradius -X
then try to login

Example configuration at Mikrotik