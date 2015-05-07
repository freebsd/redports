# Created by: Bernhard Froehlich <decke@FreeBSD.org>
# $FreeBSD$

PORTNAME=	redports-node
PORTVERSION=	0.1.1
CATEGORIES=	ports-mgmt
MASTER_SITES=	https://api.redports.org/downloads/
DISTFILES=	${PORTNAME}-${DISTVERSION}.phar ${PORTNAME}-${DISTVERSION}.phar.pubkey

MAINTAINER=	decke@FreeBSD.org
COMMENT=	Redports building node

LICENSE=	BSD2CLAUSE

RUN_DEPENDS=	${LOCALBASE}/share/certs/ca-root-nss.crt:${PORTSDIR}/security/ca_root_nss \
		poudriere:${PORTSDIR}/ports-mgmt/poudriere \
		${LOCALBASE}/bin/ccache:${PORTSDIR}/devel/ccache

NO_BUILD=	yes
USE_PHP=	curl hash json openssl pcntl phar
DEFAULT_VERSIONS=	php=56

PLIST_FILES=	bin/redports-node \
		bin/redports-node.phar \
		bin/redports-node.phar.pubkey

do-extract:
	${MKDIR} ${WRKSRC}/
	${CP} ${_DISTDIR}/redports-node-${DISTVERSION}.phar ${WRKSRC}/redports-node.phar
	${CP} ${_DISTDIR}/redports-node-${DISTVERSION}.phar.pubkey ${WRKSRC}/redports-node.phar.pubkey

do-install:
	${INSTALL_SCRIPT} ${WRKSRC}/redports-node.phar ${STAGEDIR}${PREFIX}/bin/
	${LN} -s redports-node.phar ${STAGEDIR}${PREFIX}/bin/redports-node
	${INSTALL_DATA} ${WRKSRC}/redports-node.phar.pubkey ${STAGEDIR}${PREFIX}/bin/

.include <bsd.port.mk>
