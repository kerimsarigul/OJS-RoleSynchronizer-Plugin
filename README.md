# OJS 3.3 Role Synchronizer Plugin

Bu plugin OJS 3.3'te kullanıcı rollerini dergiler arası senkronize etmek için geliştirilmiştir.

## Özellikler

- Bir dergideki kullanıcı rollerini başka bir dergiye kopyalama
- Mevcut rolleri koruma, sadece eksik rolleri ekleme
- Güvenli senkronizasyon işlemi
- Çok dilli destek (Türkçe ve İngilizce)

## Kurulum

### Yöntem 1: Plugin Galerisi Üzerinden (Önerilen)

1. Plugin dosyalarını aşağıdaki klasör yapısında oluşturun:

```
roleSynchronizer/
├── RoleSynchronizerPlugin.php
├── index.php
├── version.xml
├── locale/
│   ├── tr_TR/
│   │   └── locale.po
│   └── en_US/
│       └── locale.po
└── templates/
    └── settings.tpl
```

2. Klasörü tar.gz formatında sıkıştırın:
   ```bash
   tar -czf roleSynchronizer.tar.gz roleSynchronizer/
   ```

3. OJS yönetici paneline giriş yapın
4. **Settings > Website > Plugins > Upload a New Plugin**
5. `roleSynchronizer.tar.gz` dosyasını yükleyin
6. Plugin'i etkinleştirin

### Yöntem 2: Manuel Kurulum

1. Plugin dosyalarını `/plugins/generic/roleSynchronizer/` klasörüne kopyalayın
2. Dosya izinlerini kontrol edin (755 klasör, 644 dosya)
3. Plugin'i yönetici panelinden etkinleştirin

## Kullanım

1. Rollerin kopyalanacağı (hedef) dergiye giriş yapın
2. **Settings > Website > Plugins > Generic Plugins**
3. **Role Synchronizer** plugin'ini bulun
4. **Settings** butonuna tıklayın
5. Açılan pencerede kaynak dergiyi seçin
6. **Rolleri Senkronize Et** butonuna tıklayın
7. Onay verin

## Önemli Notlar

- ⚠️ **İşlem geri alınamaz!** Mutlaka önceden veritabanı yedeği alın
- Mevcut roller değiştirilmez, sadece eksik roller eklenir
- Aynı kullanıcı birden fazla role sahip olabilir
- Sadece Journal Manager ve Site Admin rolleri plugin'i kullanabilir

## Teknik Detaylar

- **Desteklenen OJS Versiyonu**: 3.3.x
- **Veritabanı Değişikliği**: Yok
- **Dil Desteği**: Türkçe, İngilizce

## Sorun Giderme

### Plugin yüklenmiyor
- Dosya izinlerini kontrol edin
- OJS log dosyalarını kontrol edin
- Plugin klasör yapısının doğru olduğunu kontrol edin

### Senkronizasyon çalışmıyor
- Kaynak ve hedef dergilerde aynı roller var mı kontrol edin
- PHP error log'larını kontrol edin
- Kullanıcının yeterli yetkisi var mı kontrol edin

### CSRF hatası
- Sayfayı yenileyip tekrar deneyin
- Tarayıcı cache'ini temizleyin

## Destek

Plugin ile ilgili sorunlar için:
- OJS log dosyalarını kontrol edin
- PHP error log'larını inceleyin
- Veritabanı bağlantısını kontrol edin

## Lisans

Bu plugin GPL v3 lisansı altında dağıtılmaktadır.