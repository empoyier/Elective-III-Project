function getLocalVehicleImage(brand, model) {
    const vehicleName = `${brand || ''} ${model || ''}`.toLowerCase();
    const imageMappings = [
        [/\bwigo\b/, 'toyota-wigo.png'],
        [/\bbrio\b/, 'honda-brio.png'],
        [/corolla\s+altis/, 'toyota-corolla-altis.png'],
        [/tesla.*model\s*3/, 'tesla-model-3.png'],
        [/mazda\s*3/, 'mazda-3-sedan.png'],
        [/\bcity\b/, 'honda-city-rs.png'],
        [/corvette/, 'chevrolet-corvette-c8.png'],
        [/\bfortuner\b/, 'toyota%20fortuner.jpg'],
        [/\beverest\b/, 'ford-everest.png'],
        [/\bmontero\b/, 'montero.png'],
        [/\bmirage\b/, 'mitsubishi-mirage-g4.png'],
        [/\bvios\b/, 'toyota-vios-xle.png'],
        [/\binnova\b/, 'toyota%20innova.jpg'],
        [/\bhi[\s-]?ace\b/, 'super%20randia.png'],
        [/\bstaria\b/, 'STARIA.avif'],
        [/\bioniq\b/, 'huyndai%20ioniq.jpg'],
        [/\batto\s*3\b/, '2024_Atto-3-1.jpg'],
        [/\bcivic\b/, 'honda-civic-crystal-black-pearl-671b69f3291d3.jpg'],
        [/\bmustang\b/, 'ford-mustang-iconic-silver-663b39ac8ffd1.jpg'],
        [/\bsupra\b/, 'supra.jpg'],
        [/\blexus\b|\brx\s?350\b/, 'lexus%20rx.jpg'],
        [/\bbmw\b/, 'bmw.jpg'],
        [/mercedes|e-class/, 'mercedes%20benz%20e%20class.jpg']
    ];

    const match = imageMappings.find(([pattern]) => pattern.test(vehicleName));
    return match ? `assets/images/${match[1]}` : '';
}

function getLocalVehicleImageCredit(brand, model) {
    const vehicleName = `${brand || ''} ${model || ''}`.toLowerCase();
    if (/\bwigo\b/.test(vehicleName)) return '@securitybank';
    if (/\bbrio\b/.test(vehicleName)) return '@autodeal';
    if (/\bcivic\b/.test(vehicleName)) return '@aurodeal';
    if (/\bcity\b/.test(vehicleName)) return '@hondacars';
    if (/\bmirage\b/.test(vehicleName)) return '@mitsubishiph';
    if (/\bvios\b/.test(vehicleName)) return '@toyotalucena';
    if (/\binnova\b/.test(vehicleName)) return '@toyotalucena';
    if (/\bmontero\b/.test(vehicleName)) return '@mitsubishimotorsphilippines';
    if (/\bfortuner\b/.test(vehicleName)) return '@toyotalucena';
    if (/\bhi[\s-]?ace\b/.test(vehicleName)) return '@toyotamarilaobulacan';
    if (/\bioniq\b/.test(vehicleName)) return '@roulonselectrique';
    if (/\bstaria\b/.test(vehicleName)) return '@hyundaiworldwide';
    if (/corolla\s+altis/.test(vehicleName)) return '@topgearph';
    if (/mazda\s*3/.test(vehicleName)) return '@autodeal';
    if (/\beverest\b/.test(vehicleName)) return '@fordmarikina';
    if (/corvette/.test(vehicleName)) return '@serpentinitallmadge';
    if (/\bmustang\b/.test(vehicleName)) return '@autodeal';
    if (/\bsupra\b/.test(vehicleName)) return '@carpro';
    if (/\blexus\b|\brx\s?350\b/.test(vehicleName)) return '@morganautogroup';
    if (/\bbmw\b/.test(vehicleName)) return '@arabwheels';
    if (/mercedes|e-class/.test(vehicleName)) return '@topgearph';
    if (/tesla.*model\s*3/.test(vehicleName)) return '@teslaenergy';
    if (/atto\s*3/.test(vehicleName)) return '@zigwheels';
    return '';
}
